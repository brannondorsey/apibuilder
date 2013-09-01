#API Builder Notes

##Todo

- Document all functions with function comments
- Suppress Warnings on api page

##Completed

- Make errors
- Throw error if search parameter is used but set_search hasn't been setup using `API::set_searchable();`
- Make set_default_order_by manditory and complain (through errors) if it isn't specified.