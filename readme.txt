=== Plugin Name ===
Contributors: chirphub
Donate link: http://ChirpHub.com/
Tags: chirphub
Requires at least: 3.3
Tested up to: 3.4
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This widget allows users of ChirpHub.com to format and display
information from their internet-connected devices on their WordPress blog.


== Description ==

ChirpHub is a service that allows users to connect sensors and other devices to the internet.

The ChirpHub WordPress widget allows ChirpHub users to format and display
information from their devices on their WordPress blog.


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `chirphub-widget.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates


== Frequently Asked Questions ==

= How do I add variables to my status message? =

Variables are special words that start with a "$" character.
These words are replaced by the value of the variable
when the status is displayed.

For example, to have your device status shown as:

    We are open as of Monday 8:30 AM.

your status format should look like this:

    We are $message as of $day_of_week $time.

= What variables are available to add to my status format?  =

All of the ChirpHub variables that apply to your device are available through this widget.
At present, the list includes:

* **$a00** through **$a07** are analog sensor values (range 0.00 to 100.00)
* **$d00** through **$d12** are digital port values (either 0 or 1)
* **$date** is the date in "mm/dd/yyyy" format
* **$day_of_week** is the day of the week (range "Sunday" to "Saturday")
* **$message** is either "open", "closed", or "lunch"
* **$signal_strength** is the WiFi received signal strength (range 0 to 100)
* **$time** is the time of day in "hh:mm AM" format
* **$wakeup_reason** is either "2" for timer-triggered or "3" for manually triggered

= Can I add HTML to the status message? =

Yes, just write or paste the HTML code right into the status message text box.
You can also put ChirpHub variables into your HTML, like this:

    We are <div style="font-size: 50px;"> $message </div> as of $day_of_week $time.


== Screenshots ==

(No screenshots are currently available.)


== Changelog ==

= 1.2 =
* Allow raw HTML to be pasted directly into the status message format field.

= 1.1.1 =
* Improved backwards-compatibility with version 1.0.

= 1.1 =
* Added user-formatted status messages.

= 1.0 =
* Initial version.

== Upgrade Notice ==

= 1.2 =
Any HTML entered into the status format field that was previously encoded using 
square brackets ( [  and  ] ) and single quotes ( ' ) should be changed to 
normal HTML angle brackets ("<" and ">") and double quotes. In other words,
just enter normal HTML.

= 1.1.1 = 
Minor update, no changes necessary.

= 1.1 =

This release is backwards compatible with version 1.0, so no changes should be necessary.
To prepare for future releases, it is recommended that you replace your ChirpHub URL 
with your ChirpHub device ID instead.
It is also recommended that you make use of variables in your status format. For example,
to maintain the same format as in version 1.0, add " $body." to the end of your status format.
