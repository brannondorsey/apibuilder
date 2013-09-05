#API Builder Notes

##Todo
- Suppress Warnings on api page
- Remove api.php and test_api.php before releasing project
- Make some kind of error handling for if exclude should be allowed. It should not if rows don't have ids. Maybe make a setup parameter turning it on if ids exist.
- Learn proper string sanitation for MySQL databases
- "Easily send data to and receive data from your database with simple http requests".
- Add ways that the API can be used section to README to demystify and suggest the things the API Builder Mini Lib can be used for. For instance, sending Arduino data to a server, quartzite, etc…

##Completed

- Make errors
- Throw error if search parameter is used but set_search hasn't been setup using `API::set_searchable();`
- Make set_default_order_by manditory and complain (through errors) if it isn't specified.
- Make a set_private_key method (To allow users to make their json private)
- Document in README
- Document all functions with function comments
- figure out whats up with why `API::check_API_key()` auto returns true.
- Email Chris about it
- ~~Make PHPDocumenter documentation (if I am feelin jivey)~~
- Fix `API::set_searchable()` bug
- Error handling for if search is performed on a column that is not indexed
- Check if Exclude parameter will screw things up if id is not used as an output column.
- Make sure the count parameter is using json_encode not the ugly grossness it used to.
