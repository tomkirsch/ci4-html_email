<?php

namespace Tomkirsch\HtmlEmail;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Email\Email;

class HtmlEmail
{
	const DIR_V = 'vertical';
	const DIR_H = 'horizontal';

	const SECTION_PREHEADER = 'preheader';
	const SECTION_BODY 		= 'body';
	const SECTION_FOOTER 	= 'footer';

	public $colors = [];
	public $contentHtml = [];
	public $contentPlain = [];
	public $borderRadius = '0px'; // set in config
	public $newline = "\n"; // newline that is also used in CI email lib
	public $plainSectionGlue = "\n \n"; // characters that separate the preheader/body/footer in plaintext. Set in initialize()
	public $compiledHtml = ''; // for debugging
	public $compiledPlain = ''; // debugging
	public $useFixedEmailLib; // PHP mail() can fail with CI's validation method

	protected $email; // CI Email library instance
	protected $title;
	protected $currentSection = self::SECTION_BODY;
	protected $encode = TRUE;
	protected $sections = [
		self::SECTION_PREHEADER,
		self::SECTION_BODY,
		self::SECTION_FOOTER,
	];
	protected $baseStyles = [];

	public function __construct(array $emailConfig = [], BaseConfig $config = NULL)
	{
		$this->useFixedEmailLib = $config->useFixedEmailLib ?? TRUE;
		$this->email = $this->useFixedEmailLib ? new CiEmailFix() : service('email');

		$emailConfig = array_merge([
			'mailType' => 'html',
		], $emailConfig);
		$this->email->initialize($emailConfig);

		$this->newline = $this->email->newline;

		$this->colors = $config->colors ?? [];
		$this->colors = array_merge([
			'body_bg' => '#f6f6f6', // bg of anything that falls out of the html content area
			'primary' => '#34495e', // primary button/link color
			'main_bg' => '#ffffff', // bg of main content
			'primary_text' => '#ffffff', // text color on top of primary
			'footer_color' => '#999999', // text color of footer text
		], $this->colors);

		// set border radius
		$this->borderRadius = $config->borderRadius ?? $this->borderRadius;

		// define styles for all tags
		$this->baseStyles = ['all' => ['font-family' => 'sans-serif', 'font-size' => '14px', 'font-weight' => 'normal', 'margin' => '0']];

		// now extend them based on tag
		$this->baseStyles['span'] = $this->baseStyles['all'];
		$this->baseStyles['div'] = $this->baseStyles['all'];
		$this->baseStyles['p'] = array_merge($this->baseStyles['all'], ['margin' => '0 0 15px 0']);
		$this->baseStyles['a'] = array_merge($this->baseStyles['all'], ['text-decoration' => 'underline', 'color' => $this->colors['primary']]);
		$this->baseStyles['table'] = ['width' => 'auto', 'box-sizing' => 'border-box', 'border-collapse' => 'collapse', 'mso-table-lspace' => '0pt', 'mso-table-rspace' => '0pt'];
		$this->baseStyles['td'] = array_merge($this->baseStyles['all'], ['vertical-align' => 'top', 'padding' => '5px']);
		$this->clear();
	}

	public function clear(bool $clearAttachments = FALSE)
	{
		$this->currentSection = self::SECTION_BODY;
		foreach ($this->sections as $s) {
			$this->contentHtml[$s] = '';
			$this->contentPlain[$s] = '';
		}
		$this->compiledHtml = '';
		$this->compiledPlain = '';
		$this->email->clear($clearAttachments);
		return $this;
	}

	public function setSubject(string $subject)
	{
		$this->title = htmlspecialchars($subject);
		$this->email->setSubject($subject);
		return $this;
	}

	// compile email message
	public function setMessage(string $view_file = '\\Tomkirsch\\HtmlEmail\\HtmlEmailView')
	{
		$this->compiledHtml = view($view_file, [
			'colors' => $this->colors,
			'sectionContent' => $this->contentHtml,
			'borderRadius' => $this->borderRadius,
			'title' => $this->title,
		]);
		$this->email->setMessage($this->compiledHtml);
		$this->setAltMessage();
		return $this;
	}

	public function setAltMessage()
	{
		// compile plaintext with concat strings
		$this->compiledPlain = '';
		$i = 0;
		foreach ($this->sections as $section) {
			if (!empty($this->contentPlain[$section])) {
				$this->compiledPlain .= $this->contentPlain[$section];
				if (++$i !== count($this->sections) - 1) {
					$this->compiledPlain .= $this->plainSectionGlue;
				}
			}
		}
		$this->email->setAltMessage($this->compiledPlain);
		return $this;
	}

	// add text only to plaintext
	public function plaintext(string $str)
	{
		return $this->addContent(NULL, $str);
	}

	// add decorative line for plaintext
	public function plaintextLine(string $char, ?int $char_length = NULL)
	{
		if ($char_length === NULL) $char_length = $this->email->wrapChars;
		return $this->addContent(NULL, str_repeat($char, $char_length) . $this->newline);
	}

	// add <br> for HTML and newline for plaintext
	public function brnl()
	{
		return $this->addContent('<br>', $this->newline);
	}

	// add a newline to the plaintext version
	public function nl()
	{
		return $this->addContent(NULL, $this->newline);
	}
	// add <br> to HTML-only
	public function br()
	{
		return $this->addContent('<br>', NULL);
	}

	// turn encoding on/off, or get the current value
	public function encode(?bool $bool = NULL)
	{
		if ($bool === NULL) return $this->encode;
		$this->encode = $bool;
		return $this;
	}

	// use this to change the section you're adding content to
	public function section(string $section)
	{
		$this->currentSection = $section;
		return $this;
	}
	// add a string to the current section, using encoding and strip_tags
	public function text(?string $str, ?string $plain_str = NULL)
	{
		if ($plain_str === NULL) {
			$plain_str = strip_tags($str);
		}
		if ($str && $this->encode) $str = htmlspecialchars($str);
		return $this->addContent($str, $plain_str);
	}
	// shortcuts to quickly add content to a section
	public function preheader(?string $str = NULL, ?string $plaintext = NULL)
	{
		if ($str === NULL) {
			return $this->section(self::SECTION_PREHEADER);
		}
		return $this->section(self::SECTION_PREHEADER)->text($str, $plaintext);
	}
	public function body(?string $str = NULL, ?string $plaintext = NULL)
	{
		if ($str === NULL) {
			return $this->section(self::SECTION_BODY);
		}
		return $this->section(self::SECTION_BODY)->text($str, $plaintext);
	}
	public function footer(?string $str = NULL, ?string $plaintext = NULL)
	{
		if ($str === NULL) {
			return $this->section(self::SECTION_FOOTER);
		}
		return $this->section(self::SECTION_FOOTER)->text($str, $plaintext);
	}

	// use these to easily format your copy w/ inline styles //
	public function divStart(array $styles = [], ?string $plain = NULL)
	{
		return $this->addContent('<div style="' . $this->getStyles('div', $styles) . '">', $plain);
	}
	public function divEnd(?string $plain = NULL)
	{
		return $this->addContent('</div>', $plain);
	}
	// <p> tag with styles, or plaintext with 2 newlines
	public function pStart(array $styles = [])
	{
		return $this->addContent('<p style="' . $this->getStyles('p', $styles) . '">');
	}
	public function pEnd(bool $useHtml = TRUE, bool $usePlain = TRUE)
	{
		$plain = $usePlain ? $this->newline . $this->newline : NULL;
		return $this->addContent($useHtml ? '</p>' : NULL, $plain);
	}
	public function p(?string $str, array $styles = [], ?string $plain = NULL)
	{
		if ($str !== NULL) $this->pStart($styles);
		$this->text($str, $plain);
		$this->pEnd($str !== NULL, $plain === NULL || !empty($plain));
		return $this;
	}

	// <span> tag, or just the text
	public function spanStart(array $styles = [])
	{
		return $this->addContent('<span style="' . $this->getStyles('span', $styles) . '">');
	}
	public function spanEnd()
	{
		return $this->addContent('</span>');
	}
	public function span(?string $str, array $styles = [], ?string $plain = NULL)
	{
		if ($str !== NULL) $this->spanStart($styles);
		$this->text($str, $plain);
		if ($str !== NULL) $this->spanEnd();
		return $this;
	}

	// tables
	public function tableStart(array $styles = [])
	{
		return $this->addContent('<table style="' . $this->getStyles('table', $styles) . '"><tbody>');
	}
	public function tableEnd($styles = [])
	{
		return $this->addContent('</tbody></table>');
	}
	public function trStart()
	{
		return $this->addContent('<tr>');
	}
	public function trEnd()
	{
		return $this->addContent('</tr>');
	}
	public function tdStart(array $styles = [], $attr = '')
	{
		return $this->addContent('<td style="' . $this->getStyles('td', $styles) . '" ' . stringify_attributes($attr) . '>');
	}
	public function tdEnd(bool $useHtml = TRUE, bool $usePlain = TRUE)
	{
		$plain = $usePlain ? $this->newline : NULL;
		return $this->addContent($useHtml ? '</td>' : NULL, $plain);
	}
	public function td(?string $str, array $styles = [], ?string $plain = NULL)
	{
		if ($str !== NULL) $this->tdStart($styles);
		$this->text($str, $plain);
		$this->tdEnd($str !== NULL, $plain === NULL || !empty($plain));
		return $this;
	}


	// <a> tag or label with newline and URL
	public function a(array $options)
	{
		$options = array_merge([
			'url' => site_url(),
			'label' => '',
			'styles' => [],
			'use_html' => TRUE,
			'use_plaintext' => TRUE,
			'plaintext_label' => NULL,
		], $options);

		if ($options['use_plaintext']) {
			$label = empty($options['plaintext_label']) ? $options['label'] : $options['plaintext_label'];
			$this->addContent(NULL, $label . $this->newline . '{unwrap}' . $options['url'] . '{/unwrap}' . $this->newline);
		}

		if ($options['use_html']) {
			$label = $options['label'];
			if ($this->encode) $label = htmlspecialchars($label);
			$this->addContent('<a href="{unwrap}' . $options['url'] . '{/unwrap}" style="' . $this->getStyles('a', $options['styles']) . '">' . $label . '</a>', NULL);
		}
		return $this;
	}

	// colored button link
	public function btn(array $options)
	{
		$options = array_merge([
			'url' => site_url(),
			'label' => '',
			'use_html' => TRUE,
			'use_plaintext' => TRUE,
			'plaintext_label' => NULL,
			'align' => 'left',
		], $options);

		// although its just a single button we need to create a table, so we use btn_group and wrap our parameters in arrays
		$options = array_merge($options, [
			'urls' => [$options['url']],
			'labels' => [$options['label']],
			'plaintext_labels' => empty($options['plaintext_label']) ? [] : [$options['plaintext_label']],
			'buttongroup_dir' => self::DIR_V,
		]);
		return $this->btnGroup($options);
	}


	public function btnGroup(array $options)
	{
		$options = array_merge([
			'urls' => [],
			'labels' => [],
			'plaintext_labels' => [],
			'use_html' => TRUE,
			'use_plaintext' => TRUE,
			'dir' => self::DIR_V,
			'align' => 'left',
			'width' => NULL,
			'spacer_w' => 0, // use spacer between buttons at this width (use integer, in pixels)
			'spacer_h' => 0, // use spacer between buttons at this height (use integer, in pixels)
		], $options);

		if ($options['use_plaintext']) {
			$i = 0;
			foreach ($options['urls'] as $url) {
				$label = isset($options['plaintext_labels'][$i]) ? $options['plaintext_labels'][$i] : $options['labels'][$i];
				$this->a([
					'url' => $url,
					'label' => $label,
					'use_html' => FALSE,
				]);
				$i++;
			}
		}

		if ($options['use_html']) {
			$this->addContent($this->btnGroupHtml($options));
		}

		return $this;
	}

	public function btnGroupHtml(array $data)
	{
		$out = <<<HTML
<table border="0" cellpadding="0" cellspacing="0" class="btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
	<tbody>
HTML;
		if ($data['dir'] === self::DIR_H) $out .= '<tr>';

		$i = 0;
		foreach ($data['urls'] as $url) {
			if ($data['dir'] === self::DIR_V) $out .= '<tr>';

			// spacer
			if (($data['spacer_w'] || $data['spacer_h']) && $i > 0) {
				$w = $data['spacer_w'] ? 'width="' . $data['spacer_w'] . '" ' : '';
				$ws = $data['spacer_w'] ? 'width:' . $data['spacer_w'] . 'px; ' : '';
				$h = $data['spacer_h'] ? 'height="' . $data['spacer_h'] . '" ' : '';
				$hs = $data['spacer_h'] ? 'height:' . $data['spacer_h'] . 'px; ' : '';
				$out .= '<td ' . $w . $h . ' style="' . $ws . $hs . '">&nbsp;</td>';
			}

			$out .= $this->btnTdHtml(array_merge($data, [
				'url' => $url,
				'label' => $data['labels'][$i],
			]));

			if ($data['dir'] === self::DIR_V) $out .= '</tr>';
			$i++;
		}

		if ($data['dir'] === self::DIR_H) $out .= '</tr>';
		$out .= <<<HTML
	</tbody>
</table>
HTML;
		return $out;
	}

	public function btnTdHtml($data = [])
	{
		$data = array_merge([
			'url' => base_url(),
			'label' => 'Button Label',
			'btn_bg' => $this->colors['primary'],
			'btn_text' => $this->colors['primary_text'],
			'borderRadius' => $this->borderRadius,
			'width' => NULL,
		], $data);

		$label = $data['label'];
		if ($this->encode) $label = htmlspecialchars($label);

		$width_attr = empty($data['width']) ? '' : 'width="' . $data['width'] . '"';
		$width_style = empty($data['width']) ? '' : 'width: ' . $data['width'] . ';';
		// if width given, we need to stretch the button tables out
		$table_width_attr = empty($data['width']) ? '' : 'width="100%"';
		$table_width_style = empty($data['width']) ? 'auto' : 'width:100%;';
		$a_width = empty($data['width']) ? '' : 'width:100%;';

		return <<<HTML
<td $width_attr align="{$data['align']}" style="$width_style font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">
	<table $table_width_attr border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: $table_width_style;">
		<tbody>
			<tr>
				<td style="font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: {$data['btn_bg']}; border-radius: {$data['borderRadius']}; text-align: center;">
					<a href="{unwrap}{$data['url']}{/unwrap}" target="_blank" style="display: inline-block; color: {$data['btn_text']}; background-color: {$data['btn_bg']}; border: solid 1px {$data['btn_bg']}; border-radius: {$data['borderRadius']}; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: {$data['btn_bg']}; $a_width">
					{$label}
					</a>
				</td>
			</tr>
		</tbody>
	</table>
</td>
HTML;
	}


	// core method for adding content to proper section, there is no encoding being done here
	protected function addContent(?string $str, ?string $plain = NULL)
	{
		if ($str !== NULL) {
			$this->contentHtml[$this->currentSection] .= $str;
		}
		// plain text will never appear in preheader!
		if ($this->currentSection !== self::SECTION_PREHEADER) {
			if ($plain !== NULL) {
				$this->contentPlain[$this->currentSection] .= $plain;
			}
		}
		return $this;
	}

	protected function getStyles($tag, $styles = [])
	{
		return $this->implodeStyles(array_merge($this->baseStyles[$tag], $styles));
	}

	protected function implodeStyles($styles)
	{
		$out = '';
		foreach ($styles as $key => $val) {
			$out .= $key . ':' . $val . '; ';
		}
		return $out;
	}

	// magic
	public function __get(string $name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}

		if (isset($this->email->$name)) {
			return $this->email->$name;
		}

		return null;
	}
	public function __isset(string $name): bool
	{
		if (property_exists($this, $name)) {
			return true;
		}

		if (isset($this->email->$name)) {
			return true;
		}

		return false;
	}
	public function __call(string $name, array $params)
	{
		$result = null;

		if (method_exists($this->email, $name)) {
			$result = $this->email->{$name}(...$params);
		}
		if ($result instanceof Email) {
			return $this;
		}
		return $result;
	}
}
