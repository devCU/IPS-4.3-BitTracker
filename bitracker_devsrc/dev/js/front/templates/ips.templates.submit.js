/* VIEW TEMPLATES */
ips.templates.set('bitracker.submit.screenshot', " \
	<div class='ipsGrid_span3 ipsAttach ipsImageAttach ipsPad_half ipsAreaBackground_light' id='{{id}}' data-role='file' data-fileid='{{id}}' data-fullsizeurl='{{imagesrc}}' data-thumbnailurl='{{thumbnail}}' data-fileType='image'>\
		<ul class='ipsList_inline ipsImageAttach_controls'>\
			<li><input type='radio' name='{{field_name}}_primary_screenshot' id='{{field_name}}_primary_screenshot_{{id}}' value='{{id}}' title='{{#lang}}makePrimaryScreenshot{{/lang}}' {{#default}}checked{{/default}}></li>\
			<li class='ipsPos_right' data-role='deleteFileWrapper'>\
				<input type='hidden' name='{{field_name}}_keep[{{id}}]' value='1'>\
				<a href='#' data-role='deleteFile' class='ipsButton ipsButton_verySmall ipsButton_light' data-ipsTooltip title='{{#lang}}removeScreenshot{{/lang}}'><i class='fa fa-trash-o'></i></a>\
			</li>\
		</ul>\
		<label for='{{field_name}}_primary_screenshot_{{id}}' class='ipsCursor_pointer'>\
		<div class='ipsImageAttach_thumb ipsType_center' data-role='preview' data-grid-ratio='65' data-action='insertFile' {{#thumb}}style='background-image: url( {{thumbnail}} )'{{/thumb}}>\
			{{#status}}\
				<span class='ipsImageAttach_status ipsType_light' data-role='status'>{{{status}}}</span>\
				<span class='ipsAttachment_progress'><span data-role='progressbar'></span></span>\
			{{/status}}\
			{{#thumb}}\
				{{{thumb}}}\
			{{/thumb}}\
		</div>\
		</label>\
		<h2 class='ipsType_reset ipsAttach_title ipsType_medium ipsTruncate ipsTruncate_line' data-role='title'>{{title}}</h2>\
		<p class='ipsType_light'>{{size}} &middot; <span data-role='status'>{{statusText}}</span></p>\
	</div>\
");

ips.templates.set('bitracker.submit.screenshotWrapper', " \
	<div class='ipsGrid ipsGrid_collapsePhone' data-ipsGrid data-ipsGrid-minItemSize='150' data-ipsGrid-maxItemSize='250'>{{{content}}}</div>\
");

ips.templates.set('bitracker.submit.linkedScreenshot', " \
	<li class='cBitrackerLinkedScreenshotItem'>\
		<input type='url' name='{{name}}[{{id}}]' value='{{value}}'>\
		<div class='cBitrackerLinkedScreenshotItem_block'>\
			<input type='radio' name='screenshots_primary_screenshot' value='{{id}}' title='{{#lang}}makePrimaryScreenshot{{/lang}}' data-ipsTooltip {{extra}}>\
		</div>\
		<div class='cBitrackerLinkedScreenshotItem_block'>\
			<a href='#' data-action='removeField' title='{{#lang}}removeScreenshot{{/lang}}' data-ipsTooltip><i class='fa fa-times'></i></a>\
		</div>\
	</li>\
");