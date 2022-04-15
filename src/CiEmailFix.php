<?php

namespace Tomkirsch\HtmlEmail;
/*
	PHP mail() will always fail with CI's weird validation method. This class simply overwrites, and thus should properly set the -f flag
*/

use CodeIgniter\Email\Email;

class CiEmailFix extends Email
{
	protected function validateEmailForShell(&$email)
	{
		return TRUE;
	}
}
