BookingController.php

what makes it ok:
	- Overall, most function names are clear on their intention.
	- Simple enough to read and not too complex on what the function is doing.
	
what makes it terrible:
	- Some functions have too much conditional statements that can be simplified.
	- 1 particular function name didn't follow standard convention.
	- Functional and in-line comments would have been nice to see.
	- Type hinting is also missing in most of the functions, especially on the function return (This may depend on the version of PHP being used).
	- Usage of old PHP standard like array() can be shortened by using [];
	- Some functions have the same logic.
	- Usage of deprecated helpers (This may depend on the version of PHP being used).

How would I have done it:
	- I would write the code the same way in general.
	- I would add comments functionally and in-line.
	- Shorten some of the conditional statements or add them in a new private function for readability.
	- Type hints would be also something that needs to be added (This may depend on the version of PHP being used).
	- Usage of up-to-date function helpers to simplify some of the conditional statements.

BookingRepository.php

what makes it terrible:
	- The class seems a bit overloaded and I think this repository is doing to much.
	- 1 particular function name didn't follow standard convention.
	- Most function have missing/incomplete/invalid comments.
	- A lot of conditions can be simplified.
	- There was typo on certain variable names (ex: noramlJobs => normalJobs).
	- Recurring/duplicate logic on some functions.
	- Lack of CONST usage for some variables.
	- There was a lot of unnecessary codes and a lot of the logic is either missing or commented.

How would I have done it:
	- Create another layer/service to handle more complex logic
	- Some functions can be off-loaded as a utility function elsewhere.
	- Some logic can be off-loaded to another layer of code and simplifying the logic.
	- Creating constants for reused values.
	- Type hinting is also missing in most of the functions, especially on the function return (This may depend on the version of PHP being used).
