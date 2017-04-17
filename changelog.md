# Changelog

## v1.1

- Fixed `pretty_print` URL parameter bug that disabled users from turning pretty print off if `api.php` page had it set to default on using `API::set_pretty_print()`.
- `pretty_print` only works on version 5.4.0 and up. Prior versions of PHP do not allow use of the JSON_PRETTY_PRINT constant and was causing an error. 
- Changed `header` on pages to output `application/json` instead of `text/javascript`
- Added documentation for how to insert/update using the static `Database` class in README
- `Database::execute_from_assoc()` now takes only 3 parameters as of commit [83e52cf4](https://github.com/brannondorsey/apibuilder/commit/83e52cf484043f8499a4bfd1f33f608d629bcb6f)
- `API::__construct()` now dies throwing config_error if database connection is not made
- Added `examples/` folder with two example files. More to come!
- Minor improvements to README