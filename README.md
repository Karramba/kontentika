kontentika
======

Simple social news networking service.

Current version: pre-alpha 0.1  ;)

Things already done:
  * Adding/editing/removing contents: 
    * Link entity as http URL added by user, 
    * Comment entity - user comments to Link(s)
    * Entry entity - unrelated to links and comments
  * Autoloading thumbnails
  * Adding LinkGroups - some kind of categories - groups can have only one admin (for now), admin can add moderators. Admin and moderators can edit or delete links or entries they are moderate.
  * Voting - for all kind of content - hover with voters preview
  * Link domains collecting in db (Domain entity) - for future domain blocking contents
  * Markdown in entries and comments (KontentikaParser.php - @$features)
  * Embedding youtube videos, webm videos, gfy, etc (VideoEmbedder.php - modified https://sourceforge.net/p/kawf/git/ci/95c5adb1788da088099b04b0746045286582c853/tree/user/embed-media.inc.php)
  * User avatar
  * Redis integrations - for doctrine/users 
  * Notifications

Things they need to be finished:
  * Notifications:
    * user is notified when his content was deleted. TODO: More specific informations (e.g. content title, etc)
  * Fix markdown parser - sometimes is making mess in entries, problematic with nl2br (KontentikaParser.php @transformMarkdown)
  * Groups autocompleter based on redis 
  * Session stored in redis
  * User profile - show actions, unified templates 

TODO:
  * Tests and code refactoring
  * API (perhaps rebuild website to RESTful oriented)
  * Add related links 
  * User selected link thumbnails
  * Custom group styles by adding css file
  * Custom mainpage style 
  * Block (ban) user in owned (moderated) group(s) - blocked user can't add content to group(s)
  * Block (ban) user by other user - blocked user contnet is not visible(?)
  * Subscribe groups/users 
  * Add pusher (or just websockets) for realtime notifications/content display
  * Ranking
  * Custom contents 
  * Groups can have more than one admin
  * Mark adult content 
  * ... - Lot of other things

Info:
- Mainpage minimum votes limit is set in parameters.yml (mainpage_entry_votes)
- assetic:dump is using less compiler (for very simple less templates) and uglifyjs - you **need** these packages to be installed (via npm)
- Redis is required 

Install:

`$ git clone git@github.com:kontentika/kontentika.git`

`$ composer install`

- Install less (npm install -g less) and uglifyjs (npm install -g uglify-js)
- Install redis 
