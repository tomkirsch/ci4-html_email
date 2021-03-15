<!doctype html>
<html>
<head>
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<?php if(!empty($title)): ?>
	<title><?= $title ?></title>
	<?php endif; ?>
	<style>
    @media only screen and (max-width: 620px) {
      table[class=body] h1 {
        font-size: 28px !important;
        margin-bottom: 10px !important;
      }
      table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
        font-size: 16px !important;
      }
      table[class=body] .wrapper,
            table[class=body] .article {
        padding: 10px !important;
      }
      table[class=body] .content {
        padding: 0 !important;
      }
      table[class=body] .container {
        padding: 0 !important;
        width: 100% !important;
      }
      table[class=body] .main {
        border-left-width: 0 !important;
        border-radius: 0 !important;
        border-right-width: 0 !important;
      }
      table[class=body] .btn table {
        width: 100% !important;
      }
      table[class=body] .btn a {
        width: 100% !important;
      }
      table[class=body] .img-responsive {
        height: auto !important;
        max-width: 100% !important;
        width: auto !important;
      }
    }
    @media all {
      .ExternalClass {
        width: 100%;
      }
      .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
        line-height: 100%;
      }
      	.btn-primary table td:hover,
		.btn-primary a:hover {
			background-color: #34495e !important;
			border-color: #34495e !important;
      }
    }
	</style>
</head>

<body class="" style="background-color: <?= $colors['body_bg'] ?>; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
	<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: <?= $colors['body_bg'] ?>;">
		<tr>
			<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
			<td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
				<div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 580px; padding: 10px;">

					<!-- START CENTERED WHITE CONTAINER -->
					<?php if(!empty($sectionContent['preheader'])): ?>
					<span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">
						<?= $sectionContent['preheader'] ?>
					</span>
					<?php endif; ?>
					
					<table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: <?= $colors['main_bg'] ?>; border-radius: <?= $borderRadius ?>;">

						<!-- START MAIN CONTENT AREA -->
						<tr>
							<td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">
								<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
									<tr>
										<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">
											<?= $sectionContent['body'] ?>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<!-- END MAIN CONTENT AREA -->
					</table>
					
					<?php if(!empty($sectionContent['footer'])): ?>
					<!-- START FOOTER -->
					<div class="footer" style="clear: both; margin-top: 10px; text-align: center; width: 100%;">
						<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
							<tr>
								<td class="content-block" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: <?= $colors['footer_color'] ?>; text-align: center;">
									<?= $sectionContent['footer'] ?>
								</td>
							</tr>
						</table>
					</div>
					<!-- END FOOTER -->
					<?php endif; ?>
					
					<!-- END CENTERED WHITE CONTAINER -->
				</div>
			</td>
			<td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
		</tr>
	</table>
</body>
</html>