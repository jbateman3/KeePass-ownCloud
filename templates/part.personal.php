<div class="section" id="passwords-personal">
	<h2><?php p($l->t('Passwords')); ?></h2>

	<div id="icons_show_div">
		<label>
			<input class="check" type="checkbox" id="icons_show"> <?php p($l->t('Show website icons')); ?>
		</label>
		<br><br>
	</div>
	
	<div>
		<label>
			<input class="check" type="checkbox" id="hide_usernames"> <?php p($l->t('Hide usernames')); ?>
		</label>
		<br>
		<label>
			<input class="check" type="checkbox" id="hide_passwords"> <?php p($l->t('Hide passwords')); ?>
		</label>
		<p>
			<?php p($l->t("This will show values as '*****', so you will need to click on a value to actually view it. This is useful to prevent others from making screenshots or taking photos of your password list")); ?>.
		</p>
		<br>
	</div>

	<div>
		<label>
			<input class="check" type="checkbox" id="hide_attributes"> <?php p($l->t('Hide columns') . ': |  a-z  |  A-Z  |  0-9  |  !@#  |'); ?>
		</label>
		<br>
                <label>
                        <input class="check" type="checkbox" id="hide_Strength_attributes"> <?php p($l->t('Hide column') . ': Strength'); ?>
                </label>

	</div>

	<br>

	<button id="savepersonal"><?php p($l->t('Save settings')); ?></button>
	<span class="msg-passwords"></span>

</div>
