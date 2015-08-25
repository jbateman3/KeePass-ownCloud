
<ul>
	<li class="with-counter active">
		<a href="#"><?php p($l->t("Number of passwords")); ?></a>
		<div class="app-navigation-entry-utils">
			<ul>
				<li class="app-navigation-entry-utils-counter menu_passwords_active">0</li>
			</ul>
		</div>
	</li>

	<div id="PasswordsTableSearch">
		<p><?php p($l->t("Search table")); ?>:</p>
		<input id="search_text" type="text" placeholder="<?php p($l->t("Search for")); ?>..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
		<input id="search_clear" type="button" value="<?php p($l->t("Clear")); ?>">
	</div>

	<div id="add_password_div";>
		<h3><?php p($l->t("Add new password")); ?></h3>
		<img src="<?php p(\OC::$server->getURLGenerator()->imagePath('passwords', 'blank.svg')); ?>" class="icon-link"><input type="text" id="new_address" placeholder="<?php p($l->t("Full URL (optional)")); ?>" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
		<img src="<?php p(\OC::$server->getURLGenerator()->imagePath('passwords', 'blank.svg')); ?>" class="icon-rename"><textarea id="new_notes" placeholder="<?php p($l->t("Notes (optional)")); ?>"></textarea>
		<img src="<?php p(\OC::$server->getURLGenerator()->imagePath('passwords', 'blank.svg')); ?>" class="icon-share"><input type="text" id="new_website" placeholder="<?php p($l->t("site.com or Name Inc.")); ?>" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
		<img src="<?php p(\OC::$server->getURLGenerator()->imagePath('passwords', 'blank.svg')); ?>" class="icon-user"><input type="text" id="new_username" placeholder="<?php p($l->t("Login name or e-mail")); ?>" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
		<img src="<?php p(\OC::$server->getURLGenerator()->imagePath('passwords', 'blank.svg')); ?>" class="icon-password"><input type="text" id="new_password" placeholder="<?php p($l->t("Password")); ?>" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
		<div id="generate-password">
			<p id="generate-strength"></p>
			<div id="generate-passwordtools">
				<label>
					<input class="check" type="checkbox" id="gen_lower" checked> <?php p($l->t("Lowercase characters")); ?>
				</label>
				<br>
				<label>
					<input class="check" type="checkbox" id="gen_upper" checked> <?php p($l->t("Uppercase characters")); ?>
				</label>
				<br>
				<label>
					<input class="check" type="checkbox" id="gen_numbers" checked> <?php p($l->t("Numbers")); ?>
				</label>
				<br>
				<label>
					<input class="check" type="checkbox" id="gen_special" checked> <?php p($l->t("Punctuation marks")); ?>
				</label>
				<br>
				<label>
					<input type="text" id="gen_length" value="25"> <?php p($l->t("characters")); ?>
				</label>
				<br>
			</div>
			<input id="new_generate" type="button" class="search_clear_shown" value="<?php p($l->t("Generate password")); ?>">
		</div>
		<input id="new_password_add" type="button" value="<?php p($l->t("Add password")); ?>">
	</div>

</ul>
