<?php
$days_orange = \OCP\Config::getAppValue('passwords', 'days_orange', '150');
$days_red = \OCP\Config::getAppValue('passwords', 'days_red', '365');
?>

<div id="PasswordsTable">

	<table id="PasswordsTableContent" class="sortable">
		
		<tr>
			<th id="column_website"><?php p($l->t("Title")); ?></th>
			<th id="FieldLengthCheck" class="sorttable_alpha"><?php p($l->t("Login name")); ?></th>
			<th id="FieldLengthCheck" class="sorttable_alpha"><?php p($l->t("Password")); ?></th>
			<th class="hide_Strength_attributes" id="hide3"><?php p($l->t("Strength")); ?></th>
			<th class="hide_attributes sorttable_numeric" id="hide1"><?php p($l->t("Length")); ?></th>
			<th class="hide_attributes" id="hide1">a-z</th>
			<th class="hide_attributes" id="hide1">A-Z</th>
			<th class="hide_attributes" id="hide1">0-9</th>
			<th class="hide_attributes" id="hide1">!@#</th>
			<th id="hide2"><?php p($l->t("Creation date")); ?></th>
			<th id="hide_always">ID</th>
			<th id="hide_always">User-ID</th>
			<th id="hide_always">Address</th>
			<th id="column_notes"></th>
			<th id="column_delete"></th>
			<!-- <th id="column_share"></th> -->
		</tr>

	</table>

	<br>
	<br>

</div>

<script id="content-tpl" type="text/x-handlebars-template">

	{{#each passwords}}
		<tr>
			<td><div id="FieldLengthCheck">{{ website }}</div></td>
			<td><div id="FieldLengthCheck">{{ loginname }}</div></td>
			<td><div id="FieldLengthCheck">{{ pass }}</div></td>
			<td  class="hide_Strength_attributes" id="hide3">(strength)</td>
			<td class="hide_attributes" id="hide1">(length)</td>
			<td class="hide_attributes" id="hide1">(a-z)</td>
			<td class="hide_attributes" id="hide1">(A-Z)</td>
			<td class="hide_attributes" id="hide1">(0-9)</td>
			<td class="hide_attributes" id="hide1">(!@#)</td>
			<td class="creation_date" id="hide2">{{ creation_date }}</td>
			<td class="hide_always">{{ id }}</td>
			<td class="hide_always">{{ user_id }}</td>
			<td class="hide_always">{{ address }}</td>
			<td class="icon-notes"><span>{{ notes }}</span></td>
			<td class="icon-delete"></td>
			<!--<td class="icon-share"></td>-->
		</tr>
	{{/each}}
	
</script>

<div id="emptycontent">
	<div class="icon-passwords"></div>
	<h2><?php p($l->t("Database Settings!")); ?></h2>
	<p><?php p($l->t("Database path:")); ?></p>

	<input id="database_path_text" type="text" placeholder="<?php p($l->t("database.mdbx")); ?>" size="300" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">

        <p><?php p($l->t("Database password:")); ?></p>
	<input id="password_text" type="password" placeholder="" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
	<input id="open_database" type="button" value="<?php p($l->t("Open")); ?>">
</div>
