<?php
namespace OCA\Passwords\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\Passwords\Service\PasswordService;

class PasswordController extends Controller {

    private $service;
    private $userId;

    use Errors;

    public function __construct($AppName, IRequest $request,
                                PasswordService $service, $UserId){
        parent::__construct($AppName, $request);
        $this->service = $service;
        $this->userId = $UserId;
    }

    /**
     * @NoAdminRequired
     */
    public function index() {
        return ""; // new DataResponse($this->service->findAll($this->userId));
    }


    /**
     * @NoAdminRequired
     *
     * @param string $path
     * @param string $pass
     */
    public function indexNew($path, $pass) {
        return new DataResponse($this->service->loadKeePass($this->userId, $path, $pass));; //$this->service->create($loginname, $website, $address, $pass, $notes, $this->userId);
    }

    /**
     * @NoAdminRequired
     *
     * @param string $path
     * @param string $pass
     */
    public function validatePath($path, $pass) {
        if (\OC\Files\Filesystem::file_exists ($path)){
		return "true";
	}else{
		return  $path;
	}
    }


    /**
     * @NoAdminRequired
     *
     * @param int $id
     */
    public function show($id) {
        return $this->handleNotFound(function () use ($id) {
            return $this->service->find($id, $this->userId);
        });
    }

    /**
     * @NoAdminRequired
     *
     * @param string $loginname
     * @param string $website
     */
    public function create($loginname, $website, $address, $pass, $notes) {
        return $this->service->create($loginname, $website, $address, $pass, $notes, $this->userId);
    }

    /**
     * @NoAdminRequired
     *
     * @param int $id
     * @param string $loginname
     * @param string $website
     */
    public function update($id, $loginname, $website, $address, $pass, $notes) {
        return $this->handleNotFound(function () use ($id, $loginname, $website, $address, $pass, $notes) {
            return $this->service->update($id, $loginname, $website, $address, $pass, $notes, $this->userId);
        });
    }

    /**
     * @NoAdminRequired
     *
     * @param int $id
     */
    public function destroy($id) {
        return $this->handleNotFound(function () use ($id) {
            return $this->service->delete($id, $this->userId);
        });
    }

}
