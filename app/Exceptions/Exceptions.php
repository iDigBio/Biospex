<?php

namespace Biospex\Exceptions;

class LoginRequiredException extends \UnexpectedValueException {}
class UserExistsException extends \UnexpectedValueException {}
class GroupExistsException extends \UnexpectedValueException {}
class GroupNotFoundException extends \UnexpectedValueException {}
class NameRequiredException extends \UnexpectedValueException {}
