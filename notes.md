#API Builder Notes

##Todo

- Suppress Warnings on api page
- Make a set_private_key method (To allow users to make their json private)
- Document in README
- Document all functions with function comments
- Make PHPDocumenter documentation (if I am feelin jivey)
- figure out whats up with why `API::check_API_key()` auto returns true.
- Check if Exclude parameter will screw things up if id is not used as an output column.
- Remove api.php and test_api.php before releasing project
- Error handling for if search is performed on a column that is not indexed
- Make some kind of error handling for if exclude should be allowed. It should not if rows don't have ids. Maybe make a setup parameter turning it on if ids exist.
- Make sure the count parameter is using json_encode not the ugly grossness it used to.

##Completed

- Make errors
- Throw error if search parameter is used but set_search hasn't been setup using `API::set_searchable();`
- Make set_default_order_by manditory and complain (through errors) if it isn't specified.