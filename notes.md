#API Builder Notes

##Todo

- Suppress Warnings on api page
- Make a set_private_key method (To allow users to make their json private)
- Document in README
- Document all functions with function comments
- Make PHPDocumenter documentation (if I am feelin jivey)
- figure out whats up with why `API::check_API_key()` auto returns true.

##Completed

- Make errors
- Throw error if search parameter is used but set_search hasn't been setup using `API::set_searchable();`
- Make set_default_order_by manditory and complain (through errors) if it isn't specified.