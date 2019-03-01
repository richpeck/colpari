This add-on provides a series of options for responding to chat messages and new tickets using algorithmic searches on FAQS, DOCUMENTATION etc or searches optionally powered by Google's Artificial Intelligence engine, Dialogflow.

You can configure smart responses for:

- Messages received via Facebook Messender
- Messages received via a custom dedicated smart chat box on your website
- New tickets submitted via the front-end, email, Gravity Forms integration or the REST API.

Based on messages received from FB messenger, a new ticket or a chatbox on the website, it will search the selected custom post types for keywords and send the replies back as links to the user.  
Additionally, it can use Dialogflow (former API.ai) for AI driven responses that are more flexible and intuitive but which requires slightly more complex set up.

## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [The SMART REPLIES addon] from your account(awesome-support-smart-replies.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button
- Click the `Activate` button

## Prerequisites

- Version 4.3.2 of Awesome Support or later.
- PHP 5.6 or later
- WordPress 4.7 or later

## Configuration

For Facebook, using this add-on requires that you create a FACEBOOK APPLICATION for messenger .  This is a complex process so please check our website for instructions.
For Dialogflow, you will need to follow the documentation instructions to create a GOOGLE DialogFlow application.
For Google Natural Language, you will need to follow the documentation instructions to create a GOOGLE application.

Please note: Your purchase of this product does NOT include technical support.  Setting up a FB APPLICATION or an interface to Google's Artificial Intelligence DialogFlow service (formerly API.ai) 
is generally reserved for developers so please treat this add-on as something your developer will assist with. (Or we can assist you at our regular time-and-materials rate - just contact us for more information).

## Usage

You can view usage documentation on our website here: https://getawesomesupport.com/documentation/smart-replies-integrated-ai/introduction-smart-replies/

### Change Log

-----------------------------------------------------------------------------------------
###### Version 4.1.2
- Fix: Updated all libraries to their latest versions

###### Version 4.1.1
- Fix: Store Google natural language tags after the first call instead of calling them every time the ticket was pulled up.
- Fix: Javascript error that was being thrown.
- Tweak: Add link to bottom of ticket in admin to allow GNL tags to be recalculated

###### Version 4.1.0
- New: Optionally use Google Natural Language APIs to extract keywords to drive the search functions.  Using just the extracted keywords to drive the search functions generally makes the search more accurate.

###### Version 4.0.3
- Fix: Links were sometimes returned in smart replies without a break between them.
- Fix: Slashes were sometimes escaped resulting in double and triples
- Fix: Single quotes were sometimes not processed correctly in certain messages.

###### Version 4.0.2
- Fix: Return more than 3 results when using smart chat
- Fix: A setting for the smart chat input text box was not being respected.

###### Version 4.0.1
- Fix: Disabling the smart-chat toggle did not disable the widget on the front-page of the website.
- Fix: The Dialogflow (api.ai) webhook was incorrect when installed on a multisite version of WordPress
- Fix: Settings for font/color/size of text was not working for the chatbox title.
- New: There are now separate settings for the font/color/size of text used in the user message box 

###### Version 4.0.0
- New: Smart Replies when new tickets are received
- New: Smart chat on WordPress site - you can now choose to send smart results to FB Messenger or your own website chat box or both.
- New: Product name has been changed to SMART REPLIES and associated filenames have changed as well
- Tweak: Rearranged settings tabs to accomodate new features - now uses FOUR settings tabs.
- Tweak: Settings terminology changed for certain items.
- Tweak: Renamed API.ai to reflect Google's new name - Dialogflow.

###### Version 3.0.0
- New: When using API.ai as the data source, allow it to access our post types as well.

###### Version 2.0.0
- New: Added ability to set multiple keywords for the same response.  So, "hi", "hello" can be set up as one response instead of two.
- New: Additional search types when evaluating keywords including options for SIMILAR TEXT and REGEX
- New: New search source - API.ai to bring artificial intelligence into the mix!

###### Version 1.2.0
- Strip punctuations before searching posts
- Added fallback message in case no matches found 
- Added option to match exactly or use "contains" type matches for keywords

###### Version 1.1.1
Version label for first official release in our store.

###### Version 1.0.1
- Added this readme-file
- Fixed product names
- Added licensing item id

###### Version 1.0.0
- New: Initial Release

-----------------------------------------------------------------------------------------

### Troubleshooting

We've noticed that on some NGINX configurations, the packet size needs to be increased.  Otherwise operations such as submitting a ticket 
from the SMART CHAT window will look like its "hung" due to an NGINX error that the user never sees. 
The following values seem to alleviate the issue for the NGINX servers:

proxy_buffering on;
proxy_buffer_size 128k;
proxy_buffers 4 256k;
proxy_busy_buffers_size 256k;

