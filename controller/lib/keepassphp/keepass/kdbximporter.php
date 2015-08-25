<?php

namespace  OCA\Passwords\Controller\lib\keepassphp\keepass;

use OCA\Passwords\Controller\lib\keepassphp\keepassphp;
use OCA\Passwords\Controller\lib\keepassphp\util\RessourceReader;
use OCA\Passwords\Controller\lib\keepassphp\util\CipherMcrypt;
use OCA\Passwords\Controller\lib\keepassphp\util\HashHouse;
use OCA\Passwords\Controller\lib\keepassphp\util\StringReader;
use OCA\Passwords\Controller\lib\keepassphp\util\HashedBlockReader;
use OCA\Passwords\Controller\lib\keepassphp\util\HashSHA256;
use OCA\Passwords\Controller\lib\keepassphp\util\Salsa20cipher;
use OCA\Passwords\Controller\lib\keepassphp\util\XMLStackReader;
use OCA\Passwords\Controller\lib\keepassphp\util\XMLReader;


/**
 * Class for using (essentially reading)
 * keepass databases. In charge of decoding,
 * XML-parsing the database file, and accessing its
 * content in an easy way.
 *
 * @author Louis
 */
class KdbxImporter extends Database
{
	private $file;
	private $key;
	private $randomStream;
	private $header;
	private $rawEntries;
	private $loaded;

	//Constant initatilization vector for the salsa20 cipher
	const INNER_RANDOM_SALSA20_IV = "\xE8\x30\x09\x4B\x97\x20\x5D\x2A";

	// constants used in XML parsing (name of tags, of attributes, etc)
	const XML_FILEROOT = "KeePassFile";
	const XML_META = "Meta";
	const XML_HEADERHASH = "HeaderHash";
	const XML_CUSTOMICONS = "CustomIcons";
	const XML_ICON = "Icon";
	const XML_UUID = "UUID";
	const XML_ICON_DATA = "Data";    
	const XML_ROOT = "Root";
	const XML_GROUP = "Group";
	const XML_ENTRY = "Entry";
	const XML_CUSTOMICONUUID = "CustomIconUUID";
	const XML_TAGS = "Tags";
	const XML_NOTES = "Notes";
	//const XML_ENTRY_TIMES = "Times";
        const XML_TIMES = "Times";
        const XML_CREATION_TIME = "CreationTime";
	const XML_HISTORY = "History";
	const XML_STRING = "String";
	const XML_STRING_KEY = "Key";
	const XML_STRING_VALUE = "Value";
	const XML_PROTECTED = "Protected";
	const XML_PROTECTED_TRUE = "True";
	const XML_KEY_PASSWORD = "Password";
	const XML_KEY_TITLE = "Title";
	const XML_KEY_USERNAME = "UserName";
	const XML_KEY_URL = "URL";
        const XML_KEY_NOTES = "Notes";

	public function __construct($file, iKey $key)
	{
		parent::__construct();
		$this->rawEntries = null;
		$this->key = $key;
		$this->file = $file;
		$this->loaded = false;
	}

	/***************************
	 * Database implementation *
	 ***************************/

	/**
	 * Tries to read the file $this->file, and to decode it as a KeePass 2.x
	 * database, using the key $this->key. Returns true if the operation was
	 * successful, or false otherwise. In case of success, the array
	 * $this->entries will contain the resulting list of entries.
	 *
	 * @return boolean
	 */
	public function tryLoad()
	{
		if($this->loaded)
			return $this->rawEntries != null;
		$this->loaded = true;
		
		if(!\OC\Files\Filesystem::file_exists($this->file))
		{
			KeePassPHP::raiseError("Impossible to read " . $this->file);
			return false;
		}

		KeePassPHP::printDebug("Attempting to load database from ".$this->file);

		$resReader = RessourceReader::openFile($this->file);
		if (!$resReader){
			KeePassPHP::raiseError("Unable to open file " . $this->file);
			return false;
		}

		if(!$this->tryParse($resReader))
		{
			KeePassPHP::printDebug("  ... attempt failed !");
			return false;
		}
		KeePassPHP::printDebug("  ... attempt succeeded !");
		return true;
	}

	/**
	 * Gets the current entries decoded from the database file.
	 * @return array
	 */
	public function parseEntries()
	{
		$entries = array();
		if(!$this->tryLoad())
			return $entries;
		
		foreach($this->rawEntries as $e)
		{
			if(array_key_exists(self::XML_UUID, $e))
			{
				$entry = array();
				$entry[parent::KEY_TITLE] = parent::getIfSet($e,
						self::XML_KEY_TITLE, parent::DEFAULT_TITLE);
				$entry[parent::KEY_CUSTOMICON] = parent::getIfSet($e,
						self::XML_CUSTOMICONUUID);
				$entry[parent::KEY_TAGS] = parent::getIfSet($e, self::XML_TAGS);
				$entry[parent::KEY_URL] = parent::getIfSet($e, self::XML_KEY_URL);
				$entry[parent::KEY_USERNAME] = parent::getIfSet($e,
						self::XML_KEY_USERNAME);
				$entry[parent::KEY_NOTES] = parent::getIfSet($e, self::XML_NOTES);
                                $entry[parent::KEY_CREATION_TIME] = parent::getIfSet($e, self::XML_CREATION_TIME);
				$entries[$e[self::XML_UUID]] = $entry;
			}
		}
		return $entries;
	}

	/**
	 * Returns the password associated with the entry with the UUID $uuid, or
	 * null if not found.
	 * @param string $uuid
	 * @return string
	 */
	public function getPassword($uuid)
	{
		if(!$this->tryLoad())
			return null;
		
		foreach($this->rawEntries as $e)
		{
			if(array_key_exists(self::XML_UUID, $e)
				&& array_key_exists(self::XML_KEY_PASSWORD, $e)
				&& $e[self::XML_UUID] == $uuid)
					return $e[self::XML_KEY_PASSWORD];
		}
		return null;
	}

	/*******************
	 * Private methods *
	 *******************/


	/**
	 * Implementation of gzdecode which just works.
	 * See http://www.php.net/manual/en/function.gzdecode.php#82930
	 * @param string $data
	 * @param string $filename
	 * @param string $error
	 * @param int $maxlength
	 * @return null|boolean
	 */
	private function gzdecode2($data, &$filename = '', &$error = '', $maxlength = null) {
		$len = strlen($data);
		if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
			$error = "Not in GZIP format.";
			return null;  // Not GZIP format (See RFC 1952)
		}
		$method = ord(substr($data, 2, 1));  // Compression method
		$flags = ord(substr($data, 3, 1));  // Flags
		if ($flags & 31 != $flags) {
			$error = "Reserved bits not allowed.";
			return null;
		}
		// NOTE: $mtime may be negative (PHP integer limitations)
		$mtime = unpack("V", substr($data, 4, 4));
		$mtime = $mtime[1];
		$xfl = substr($data, 8, 1);
		$os = substr($data, 8, 1);
		$headerlen = 10;
		$extralen = 0;
		$extra = "";
		if ($flags & 4) {
			// 2-byte length prefixed EXTRA data in header
			if ($len - $headerlen - 2 < 8) {
				return false;  // invalid
			}
			$extralen = unpack("v", substr($data, 8, 2));
			$extralen = $extralen[1];
				if ($len - $headerlen - 2 - $extralen < 8) {
				return false;  // invalid
		}
			$extra = substr($data, 10, $extralen);
			$headerlen += 2 + $extralen;
		}
		$filenamelen = 0;
		$filename = "";
		if ($flags & 8) {
			// C-style string
			if ($len - $headerlen - 1 < 8) {
				return false; // invalid
			}
			$filenamelen = strpos(substr($data, $headerlen), chr(0));
			if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
				return false; // invalid
			}
			$filename = substr($data, $headerlen, $filenamelen);
			$headerlen += $filenamelen + 1;
		}
		$commentlen = 0;
		$comment = "";
		if ($flags & 16) {
			// C-style string COMMENT data in header
			if ($len - $headerlen - 1 < 8) {
				return false;    // invalid
			}
			$commentlen = strpos(substr($data, $headerlen), chr(0));
			if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
				return false;    // Invalid header format
			}
			$comment = substr($data, $headerlen, $commentlen);
			$headerlen += $commentlen + 1;
		}
		$headercrc = "";
		if ($flags & 2) {
			// 2-bytes (lowest order) of CRC32 on header present
			if ($len - $headerlen - 2 < 8) {
				return false;    // invalid
			}
			$calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
			$headercrc = unpack("v", substr($data, $headerlen, 2));
			$headercrc = $headercrc[1];
			if ($headercrc != $calccrc) {
				$error = "Header checksum failed.";
					return false;    // Bad header CRC
			}
			$headerlen += 2;
		}
		// GZIP FOOTER
		$datacrc = unpack("V", substr($data, -8, 4));
		$datacrc = sprintf('%u', $datacrc[1] & 0xFFFFFFFF);
		$isize = unpack("V", substr($data, -4));
		$isize = $isize[1];
		// decompression:
		$bodylen = $len - $headerlen - 8;
			if ($bodylen < 1) {
			// IMPLEMENTATION BUG!
			return null;
		}
		$body = substr($data, $headerlen, $bodylen);
		$data = "";
		if ($bodylen > 0) {
			switch ($method) {
					case 8:
					// Currently the only supported compression method:
					$data = gzinflate($body, $maxlength);
					break;
				default:
					$error = "Unknown compression method.";
					return false;
			}
		}  // zero-byte body content is allowed
		// Verifiy CRC32
		$crc = sprintf("%u", crc32($data));
		$crcOK = $crc == $datacrc;
		$lenOK = $isize == strlen($data);
		if (!$lenOK || !$crcOK) {
			$error = ( $lenOK ? '' : 'Length check FAILED. ') .
					( $crcOK ? '' : 'Checksum FAILED.');
			return false;
		}
		return $data;
	}


	/**
	 * Tries to parse a database accessed through the given Reader $reader.
	 * Returns true if the parsing succeeds, in which case the attribute
	 * $this->entries will contain the result of the parsing, as an array
	 * of entries ; or returns false otherwise.
	 * @param Reader $reader
	 * @return boolean
	 */
	private function tryParse(Reader $reader)
	{
		$this->header = new Header();
		$this->header->parse($reader);
		if(!$this->header->check())
		{
			KeePassPHP::printDebug("Header check failed !");
			return false;
		}

		$key = $this->transformKey();

		$cipher = $this->header->cipher;
		$cipher->setMode('cbc');
		$cipher->setKey($key);
		$cipher->setIV($this->header->encryptionIV);
		$cipher->setPadding(CipherMcrypt::PK7_PADDING);
		$cipher->load();
		$decrypted = $cipher->decrypt($reader->readToTheEnd());
		$cipher->unload();

		if($decrypted == false || strcmp(substr($decrypted, 0,
			Header::STARTBYTES_LEN), $this->header->startBytes) != 0)
		{
			KeePassPHP::printDebug("Decryption problem !");
			return false;
		}

		$decryptedReader = new StringReader(substr($decrypted, Header::STARTBYTES_LEN));
		$hashedReader = new HashedBlockReader($decryptedReader, new HashSHA256());
		$decoded = $hashedReader->readToTheEnd();
		if($decoded == null || strlen($decoded) == 0)
		{
			KeePassPHP::printDebug("A problem occured when reading hashed blocks.");
			return false;
		}

		if($this->header->compression == Header::COMPRESSION_GZIP)
		{
			$decoded = $this->gzdecode2($decoded);
			if($decoded == null || strlen($decoded) == 0)
			{
				KeePassPHP::printDebug("UnGzipping failed !");
				return false;
			}
		}

		if($this->header->innerRandomStream == Header::INNER_RANDOM_SALSA20)
			$this->randomStream = new Salsa20cipher(
				HashHouse::hash ($this->header->randomStreamKey),
				self::INNER_RANDOM_SALSA20_IV);

		return $this->tryXMLParse($decoded);
	}

	/**
	 * Tries to parse the given string $xmlsource, assumed to be data formatted
	 * in XML, with the format of a KeePass 2.x database. Returns true
	 * if the parsing succeeds, or false otherwise ; in case of success,
	 * the attribute $this->entries will contain the result of the parsing,
	 * as an array of entries.
	 * @param string $xmlsource
	 * @return boolean
	 */
	private function tryXMLParse($xmlsource)
	{
		$xml = new XMLStackReader();
		if(!$xml->XML($xmlsource))
		{
			$xml->close();
			return false;
		}

		if(!$xml->read() || $xml->r->name != self::XML_FILEROOT)
		{
			$xml->close();
			return false;
		}

		$expectedParentsMeta = array(self::XML_FILEROOT, self::XML_META);
		if(!$xml->readUntilParentsBe($expectedParentsMeta))
		{
			$xml->close();
			return false;
		}

		$isHeaderChecked = false;
		$d = $xml->r->depth;
		while($xml->isInSubtree($d))
		{
			if($xml->r->name == self::XML_HEADERHASH)
			{
				$hash = base64_decode($this->readTextValueFromXML($xml));
				if(strcmp($hash, $this->header->headerHash) != 0)
					KeePassPHP::printDebug ("Bad HeaderHash !");
				$isHeaderChecked = true;
			}
			elseif($xml->r->name == self::XML_CUSTOMICONS)
			{
				foreach($xml->readInnerXML($xml->r->depth) as $icon)
				{
					$uuid = null;
					$data = null;
					if($icon[XMLStackReader::NODENAME] == self::XML_ICON
					  && $this->tryReadTextValueFromArray($icon[XMLStackReader::INNER],
						self::XML_UUID, $uuid)
					  && $this->tryReadTextValueFromArray($icon[XMLStackReader::INNER],
						self::XML_ICON_DATA, $data))
						$this->icons->addIcon($uuid, $data);
				}
			}
		}

		if(!$isHeaderChecked)
			KeePassPHP::printDebug("Did not found HeaderHash text node...");

		$this->rawEntries = array();
		$expectedParents = array(self::XML_GROUP, self::XML_ENTRY);
		while($xml->readUntilParentsBe($expectedParents))
		{
			$entry = array();
			$d = $xml->r->depth;
			while($xml->isInSubtree($d))
			{
				if($xml->r->name == self::XML_UUID)
					$entry[self::XML_UUID] = bin2hex(base64_decode($this->readTextValueFromXML($xml)));
				elseif($xml->r->name == self::XML_CUSTOMICONUUID)
					$entry[self::XML_CUSTOMICONUUID] = $this->readTextValueFromXML($xml);
				elseif($xml->r->name == self::XML_TAGS)
					$entry[self::XML_TAGS] = $this->readTextValueFromXML($xml);										
                                elseif($xml->r->name == self::XML_TIMES)
                                {
                                        $value = null;
                                        $isHistory = $xml->isAncestor(self::XML_HISTORY);
                                        $inner = $xml->readInnerXML($xml->r->depth);
                                        if($this->tryReadTextValueFromArray($inner, self::XML_CREATION_TIME, $value))
                                                if($value != null && !$isHistory)
                                                        $entry[self::XML_CREATION_TIME] = $value;

                                }
				elseif($xml->r->name == self::XML_STRING)
				{
					$key = null;
					$value = null;
					$isHistory = $xml->isAncestor(self::XML_HISTORY);
					$inner = $xml->readInnerXML($xml->r->depth);
					if($this->tryReadTextValueFromArray($inner, self::XML_STRING_VALUE, $value)
					  && $this->tryReadTextValueFromArray($inner, self::XML_STRING_KEY, $key))
						if($key != null && $value != null && !$isHistory)
							$entry[$key] = $value;
				}
			}
			if(count($entry) > 0)
				array_push($this->rawEntries, $entry);
		}
		$xml->close();
		return true;
	}

	/**
	 * Tries to read the inner text of a XML node, assuming that the given
	 * XMLStackReader $xml is currently at this node, and that the only child
	 * of the node is a text node. Uses $this->randomStream to decode the
	 * text if needed.
	 * @param XMLStackReader $xml
	 * @return string|\null
	 */
	private function readTextValueFromXML(XMLStackReader $xml)
	{
		if($xml->r->hasAttributes && $xml->r->moveToAttribute(self::XML_PROTECTED))
		{
			if($xml->r->value == self::XML_PROTECTED_TRUE)
			{
				$xml->r->moveToElement();
				if($xml->isTextInside())
				{
					$v = base64_decode($xml->r->value);
					return $this->randomStream->dencrypt($v);
				}
			}
		}
		elseif($xml->isTextInside())
			return $xml->r->value;
		return null;
	}

	/**
	 * Tries to find a child node named $name in the given array $array of nodes
	 * (assumed to be returned by the method $xml->readInnerXML()), and returns
	 * true in case of success, or false otherwise. If the node is found,
	 * $result will contain what is inside :
	 *  - either null if the nodes contains nothing
	 *  - either a string if the node contains a text node
	 *  - either the subtree (as an array) of the node
	 * @param array $array
	 * @param string $name
	 * @param mixed $result
	 * @return boolean
	 */
	private function tryReadTextValueFromArray($array, $name, &$result)
	{
		if(XMLStackReader::tryGetChild($array, $name, $result))
		{
			if($result[XMLStackReader::INNER] == null)
				$result = null;
			elseif(is_string($result[XMLStackReader::INNER]))
			{
				$a = $result[XMLStackReader::ATTRIBUTES];
				if($a != null && array_key_exists(self::XML_PROTECTED, $a) &&
					$a[self::XML_PROTECTED] == self::XML_PROTECTED_TRUE)
				{
					$v = base64_decode($result[XMLStackReader::INNER]);
					$result = $this->randomStream->dencrypt($v);
				}
				else
					$result = $result[XMLStackReader::INNER];
			}
			return true;
		}
		return false;
	}

	/**
	 * Returns as a binary string the final AES key used for decrypting
	 * the database file, computed from the seeds and the master composite key.
	 * @return string
	 */
	private function transformKey()
	{
		$seed = $this->header->transformSeed;
		$keyHash = $this->key->getHash();
		/// does not yet support the case rounds >> 2**31
		$rounds = $this->header->rounds->asInt();

		$AESEncryptor = new CipherMcrypt(CipherMcrypt::AES128, 'ecb', $seed);
		$AESEncryptor->load();
		for($i = 0 ; $i < $rounds ; $i++)
			$keyHash = $AESEncryptor->encrypt($keyHash);
		$AESEncryptor->unload();

		$finalKey = HashHouse::hash($keyHash);
		$aesKey = HashHouse::hash($this->header->masterSeed . $finalKey);

		return $aesKey;
	}
}

?>
