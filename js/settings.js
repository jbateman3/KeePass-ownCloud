
// ADMIN SETTINGS
// backup_allow
//		Allow unencrypted backups to be downloaded by users
// days_orange
//		Days from which creation date (and password) gets orange color
// days_red
//		Days from which creation date (and password) gets red color
// https_check
//		Check for secure connection before activating app
// icons_allowed
//		Allow users to view website icons, by sending IP to another server
// icons_service
//		Service used for website icons: Google (ggl), DuckDuckGo (ddg)

// USER SETTINGS
// hide_attributes
//		Hide the attributes a-z, A-Z, 0-9, !@#
// hide_passwords
//		Hide passwords by showing them as '*****'
// hide_usernames
//		Hide usernames by showing them as '*****'
// icons_show
//		Show website icons, using service selected by admin

$(document).ready(function() {

	var Settings = function(baseUrl) {
		this._baseUrl = baseUrl;
		this._settings = [];
	};

	Settings.prototype = {
		load: function() {
			var deferred = $.Deferred();
			var self = this;
			$.ajax({
				url: this._baseUrl,
				method: 'GET',
				async: false
			}).done(function( settings ) {
				self._settings = settings;
			}).fail(function() {
				deferred.reject();
			});
			return deferred.promise();
		},

		setUserKey: function(key, value) {
			var deferred = $.Deferred();
			$.ajax({
				url: this._baseUrl + '/' + key + '/' + value,
				method: 'POST'
			}).done(function( data ) {
				deferred.resolve(data);
			}).fail(function() {
				$('.msg-passwords').addClass("msg_error");
				$('.msg-passwords').text(t('passwords', 'Error while saving field') + ' ' + key + '!');
				deferred.reject();
			});
		},

		setAdminKey: function(key, value) {
			var deferred = $.Deferred();
			$.ajax({
				// route is based on URL!
				// /admin1/ only saves a userkey with value 'admin1'
				// so /admin1/admin2 is needed. Ugly but functional.
				url: this._baseUrl + '/' + key + '/' + value + '/admin1/admin2',
				method: 'POST'
			}).done(function( data ) {
				deferred.resolve(data);
			}).fail(function() {
				$('.msg-passwords').addClass("msg_error");
				$('.msg-passwords').text(t('passwords', 'Error while saving field') + ' ' + key + '!');
				deferred.reject();
			});
		},

		getKey: function(key) {
			for (var k in this._settings)
			{
				if (k == key)
					return this._settings[k];
			}
		},
		getAll: function() {
			return this._settings;
		}
	};

	var settings = new Settings(OC.generateUrl('/apps/passwords/settings'));
	settings.load();

// ADMIN SETTINGS

	// fill the boxes
	$('#https_check').prop('checked', (settings.getKey('https_check').toLowerCase() == 'true'));
	$('#backup_allowed').prop('checked', (settings.getKey('backup_allowed').toLowerCase() == 'true'));
	
	$('#icons_allowed').prop('checked', (settings.getKey('icons_allowed').toLowerCase() == 'true'));
	if (settings.getKey('icons_service') == 'ddg') {
		$('#ddg_value').prop('checked', 'true'); 
	}
	if (settings.getKey('icons_service') == 'ggl') {
		$('#ggl_value').prop('checked', 'true'); 
	}
	updateIconService();

	$('#days_orange').val(settings.getKey('days_orange'));
	$('#days_red').val(settings.getKey('days_red'));
	updateOrange();
	updateRed();

	// save it all
	$('#saveadmin').click(function() {

		// test for numeric values in days
		if(!isNumeric($('#days_orange').val()) 
			|| !isNumeric($('#days_red').val())) {
				alert(t('passwords', 'Fill in a valid number for the days.'));
				return false;
		}
		// test if red < orange
		if($('#days_orange').val() >= $('#days_red').val()) {
			alert(t('passwords', 'The days from red should be higher than the days from orange.'));
			return false;
		}

		$('.msg-passwords').text(t('passwords', 'Saving...'));

		settings.setAdminKey('https_check', $('#https_check').is(':checked'));
		settings.setAdminKey('backup_allowed', $('#backup_allowed').is(':checked'));

		settings.setAdminKey('icons_allowed', $('#icons_allowed').is(':checked'));
		if ($('#ddg_value').is(':checked')) {
			settings.setAdminKey('icons_service', 'ddg');	
		}
		if ($('#ggl_value').is(':checked')) {
			settings.setAdminKey('icons_service', 'ggl');	
		}
		settings.setAdminKey('days_orange', $('#days_orange').val());
		settings.setAdminKey('days_red', $('#days_red').val());

		// no error, so:
		if ($('.msg-passwords').text = t('passwords', 'Saving...')) {
			$('.msg-passwords').addClass('msg_success');
			$('.msg-passwords').text(t('passwords', 'Saved!'));	
			setTimeout(function() {
				$('.msg-passwords').text('');
				$('.msg-passwords').removeClass('msg_success');	
			}, 3000);
		}
	});

	$('#days_red').keyup(function() {
		updateRed();
	});

	$('#days_orange').keyup(function() {
		updateOrange();
	});

	$('#icons_allowed').click(function() {
		updateIconService();
	});

// PERSONAL SETTINGS
	
	// fill the boxes
	if (settings.getKey('icons_allowed').toLowerCase() == 'true') {
		$('#icons_show').prop('checked', (settings.getKey('icons_show').toLowerCase() == 'true'));
	} else {
		$('#icons_show_div').remove();
	}

	$('#hide_usernames').prop('checked', (settings.getKey('hide_usernames').toLowerCase() == 'true'));
	$('#hide_passwords').prop('checked', (settings.getKey('hide_passwords').toLowerCase() == 'true'));
	$('#hide_attributes').prop('checked', (settings.getKey('hide_attributes').toLowerCase() == 'true'));
	$('#hide_Strength_attributes').prop('checked', (settings.getKey('hide_Strength_attributes').toLowerCase() == 'true'));	

	// save it all
	$('#savepersonal').click(function() {

		$('.msg-passwords').text(t('passwords', 'Saving...'));

		settings.setUserKey('icons_show', $('#icons_show').is(':checked'));
		settings.setUserKey('hide_usernames', $('#hide_usernames').is(':checked'));
		settings.setUserKey('hide_passwords', $('#hide_passwords').is(':checked'));
		settings.setUserKey('hide_attributes', $('#hide_attributes').is(':checked'));
                settings.setUserKey('hide_Strength_attributes', $('#hide_Strength_attributes').is(':checked'));

		// no error, so:
		if ($('.msg-passwords').text = t('passwords', 'Saving...')) {
			$('.msg-passwords').addClass('msg_success');
			$('.msg-passwords').text(t('passwords', 'Saved!'));	
			setTimeout(function() {
				$('.msg-passwords').text('');
				$('.msg-passwords').removeClass('msg_success');	
			}, 3000);
		}
	});

});

function updateRed() {
	$('#daysRed').text(
		t('passwords', 'Red') 
		+ ': ' 
		+ t('passwords', 'after') 
		+ ' ' 
		+ (Number($('#days_red').val()) + 1) 
		+ ' ' 
		+ t('passwords', 'days')
	);
}
function updateOrange() {
	$('#daysOrange').text(
		t('passwords', 'Orange') 
		+ ': ' 
		+ (Number($('#days_orange').val()) + 1) 
		+ ' ' 
		+ t('passwords', 'to')
	);
}
function updateIconService() {
	if ($('#icons_allowed').prop('checked')) {
		$('#ddg_value').prop("checked", true);
		$('#ggl_value').prop("enabled", true);
		$('#ddg_value').prop("enabled", true);
		$('#ggl_value').prop("disabled", false);
		$('#ddg_value').prop("disabled", false);
	} else {
		$('#ggl_value').prop("checked", false);
		$('#ddg_value').prop("checked", false);
		$('#ggl_value').prop("enabled", false);
		$('#ddg_value').prop("enabled", false);
		$('#ggl_value').prop("disabled", true);
		$('#ddg_value').prop("disabled", true);
	}
}
function isNumeric(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}
