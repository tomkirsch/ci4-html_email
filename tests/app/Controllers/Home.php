<?php namespace App\Controllers;

class Home extends BaseController
{
	public function index()
	{
		$htmlEmail = service('htmlEmail');
		$htmlEmail
			->setSubject('A Test Email for YOU!')
			->section($htmlEmail::SECTION_PREHEADER)
			->p('This is the preheader. It shows up in email previews (sometimes), but not the body.')
			->section($htmlEmail::SECTION_BODY)
			->p('Well Hello There!', [
				'text-align'=>'center',
				'font-size'=>'20px',
				'font-weight'=>'bold',
			])
			->p('This is a test email that uses HTML standard from 1992, because email applications cannot get their shit together. Hopefully this looks the same in most programs.')
			->pStart(['text-align'=>'center'])
				->a([
					'url'=>'https://google.com',
					'label'=>'Plain Link',
				])
			->pEnd()
			->pStart(['text-align'=>'center'])
				->btn([
					'url'=>'https://google.com',
					'label'=>'Button',
					'align'=>'center',
				])
			->pEnd()
			->p('You can make button groups that are horizontal.')
			->btnGroup([
				'urls'=> ['https://google.com', 'https://bing.com'],
				'labels'=>['Button Group 1', 'Button Group 2'],
				'dir'=>$htmlEmail::DIR_H,
				'align'=>'center',
				'width'=>'50%',
				'spacer_w'=>10,
			])
			->p('You can also make button groups that are vertical.')
			->btnGroup([
				'urls'=> ['https://google.com', 'https://bing.com'],
				'labels'=>['Button Group 1', 'Button Group 2'],
				'dir'=>$htmlEmail::DIR_V,
				'align'=>'center',
				'width'=>'50%',
				'spacer_v'=>10,
			])
			->plaintextLine('=')
			->p(NULL, [], 'A SECRET MESSAGE FOR PLAINTEXT USERS ONLY!')
			->plaintextLine('=')
			->section('footer')
			->p('You were sent this email because you stupidly signed up for our mailing list. Deal with it.')
			->setMessage()
		;
		// your normal CI email stuff
		$htmlEmail
			->setFrom('me@example.com')
			->setTo('you@example.com')
			//->send();
		;
		$out = $htmlEmail->compiledHtml;
		$out = str_replace('</body>', '<pre>'.$htmlEmail->compiledPlain.'</pre></body>', $out);
		return $out;
	}
}
