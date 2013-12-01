<?php 
/*
 * If this file is opened directly (e.g. for preview purpose), we load WP.
 * So you can use WP functions ecc.
 */
if ( !defined('ABSPATH') ) include_once('../../../../wp-load.php'); 

// Then, you can use 2 available objects: $newsletter, $recipient
// Uncomment next 2 line to view available properties:
//echo "<br />\n<pre>Newsletter=".print_r( $newsletter,true )."</pre>";
//echo "<br />\n<pre>Recipient=".print_r( $recipient,true )."</pre>";

// E.g. the newsletter (post) ID is: $newsletter->ID
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />	
		<title>[SITE-NAME]</title>
		<!--general stylesheet-->
		<style type="text/css">
			p { padding: 0; margin: 0; }
			h1, h2, h3, p, li { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; }
			td { vertical-align:top;}
			ul, ol { margin: 0; padding: 0;}
			.title, .date {
				text-shadow: #8aa3c6 0px 1px 0px;
			}
			
			.textshadow {
				text-shadow: #ffffff 0px 1px 0px;
			}
			.trxtshadow-2 {
				text-shadow: #768296 0px -1px 0px;
			}
		</style>
	</head>
	<body marginheight="0" topmargin="0" marginwidth="0" leftmargin="0" background="" style="margin: 0px; background-color: #eee; background-image: url(''); background-repeat: repeat;" bgcolor="">
		<table cellspacing="0" border="0" cellpadding="0" width="100%" >
			<tbody>
				<tr valign="top">
					<td valign="top"><!--container-->
						<table cellspacing="0" cellpadding="0" border="0" align="center" width="626">
							<tbody>
								<tr>
									<td valign="middle" bgcolor="#546781" height="97" background="campaignmonitor_mute/header-bg.jpg" style="vertical-align: middle;background: #C8CFD8 url('campaignmonitor_mute/header-bg.jpg') repeat-x right top;">
										<table cellspacing="0" cellpadding="0" border="0" align="center" width="555" height="97">
											<tbody>
												<tr>
													<td valign="middle" width="36" style="vertical-align:middle; text-align: left;">
														<img width="29" height="29" src="campaignmonitor_mute/19gear.png" style="margin:0; margin-top: 4px; display: block;" alt=""  />
													</td>
													<td valign="middle" style="vertical-align: middle; text-align: left;">
														<h1 class="title" style="margin:0; padding:0; font-size:30px; font-weight: normal; color: #192c45;">
															<span style="font-weight: bold;">news</span> [SITE-NAME]
														</h1>
													</td>
													<td width="55" valign="middle" style="vertical-align:middle; text-align: center;">
														<h2 class="date" style="margin:0; padding:0; font-size:13px; font-weight: normal; color: #192c45; text-transform: uppercase; font-weight: bold; line-height:1;">
															<currentmonthname>
														</h2>
														<h2 class="date" style="margin:0; padding:0; font-size:23px; font-weight: normal; color: #192c45; font-weight: bold;">
															 <currentyear>
														</h2>
													</td>
													
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td valign="top">
										<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%" bgcolor="#ffffff" style="border-width: 3px; border-color: #ffffff; border-style: solid;">
											<tbody>
												<tr>
													<td width="100%" valign="top" bgcolor="#eef0f3" style="border-bottom-width: 3px; border-bottom-color: #ffffff; border-bottom-style: solid;"><!--content-->
										<!--article--><table cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
															<tbody>
																<tr>
																	<td valign="top">
																		<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
																			<tbody>
																				<tr>
																					<td height="49" width="100%" valign="middle" bgcolor="#c8cfd8" background="campaignmonitor_mute/article-title-bg.jpg" style="vertical-align:middle; border-left-width: 1px; border-left-color: #BAC2CC; border-left-style: solid; border-right-width: 1px; border-right-color: #BAC2CC; border-right-style: solid; border-bottom-width: 1px; border-bottom-color: #98a3b4; border-bottom-style: solid; border-top-width: 1px; border-top-color: #BAC2CC; border-top-style: solid;background: #C8CFD8 url('campaignmonitor_mute/article-title-bg.jpg') repeat-x right top;">
																						<h3 class="textshadow" style="margin:0; margin-left: 17px; padding:0; font-size: 18px; font-weight: normal; color:#324258;">
																							[TITLE]
																						</h3>
																					</td>
																				</tr>
																				<tr>
																					<td valign="top" bgcolor="#edeff2" style="padding-top: 20px; padding-bottom: 15px; padding-left: 21px; padding-right: 21px; border-left-width: 1px; border-left-color: #bac2cc; border-left-style: solid; border-right-width: 1px; border-right-color: #bac2cc; border-right-style: solid; border-bottom-width: 3px; border-bottom-color: #ffffff; border-bottom-style: solid; border-top-width: 1px; border-top-color: #ffffff; border-top-style: solid;">
																						<p style="font-size: 12px; line-height: 20px; font-weight: normal; color: #56667d; margin: 0; margin-bottom: 10px;">
																							[THUMB] [CONTENT]
																						</p>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																</tr>
																
																<tr>
																	<td valign="top">
																		<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
																			<tbody>
																				<tr>
																					<td height="49" width="100%" valign="middle" bgcolor="#c8cfd8" background="campaignmonitor_mute/article-title-bg.jpg" style="vertical-align:middle; border-left-width: 1px; border-left-color: #BAC2CC; border-left-style: solid; border-right-width: 1px; border-right-color: #BAC2CC; border-right-style: solid; border-bottom-width: 1px; border-bottom-color: #98a3b4; border-bottom-style: solid; border-top-width: 1px; border-top-color: #BAC2CC; border-top-style: solid; background: #C8CFD8 url('campaignmonitor_mute/article-title-bg.jpg') repeat-x right top;">
																						<h3 class="textshadow" style="margin:0; margin-left: 17px; padding:0; font-size: 18px; font-weight: normal; color:#324258;">
																							[POST-TITLE]
																						</h3>
																					</td>
																				</tr>
																				<tr>
																					<td valign="top" bgcolor="#edeff2" style="padding-top: 20px; padding-bottom: 15px; padding-left: 21px; padding-right: 21px; border-left-width: 1px; border-left-color: #bac2cc; border-left-style: solid; border-right-width: 1px; border-right-color: #bac2cc; border-right-style: solid; border-bottom-width: 3px; border-bottom-color: #ffffff; border-bottom-style: solid; border-top-width: 1px; border-top-color: #ffffff; border-top-style: solid;">
																						<p style="font-size: 12px; line-height: 20px; font-weight: normal; color: #56667d; margin: 0; margin-bottom: 10px;">
																							[POST-THUMB] [POST-CONTENT]
																						</p>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																</tr>
																
																<tr>
																	<td valign="top">
																		<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
																			<tbody>
																				<tr>
																					<td height="49" width="100%" valign="middle" bgcolor="#c8cfd8" background="campaignmonitor_mute/article-title-bg.jpg" style="vertical-align:middle; border-left-width: 1px; border-left-color: #BAC2CC; border-left-style: solid; border-right-width: 1px; border-right-color: #BAC2CC; border-right-style: solid; border-bottom-width: 1px; border-bottom-color: #98a3b4; border-bottom-style: solid; border-top-width: 1px; border-top-color: #BAC2CC; border-top-style: solid; background: #C8CFD8 url('campaignmonitor_mute/article-title-bg.jpg') repeat-x right top;">
																						<h3 class="textshadow" style="margin:0; margin-left: 17px; padding:0; font-size: 18px; font-weight: normal; color:#324258;">
																							Latest Newsletters
																						</h3>
																					</td>
																				</tr>
																				<tr>
																					<td valign="top" bgcolor="#edeff2" style="padding-top: 20px; padding-bottom: 15px; padding-left: 21px; padding-right: 21px; border-left-width: 1px; border-left-color: #bac2cc; border-left-style: solid; border-right-width: 1px; border-right-color: #bac2cc; border-right-style: solid; border-top-width: 1px; border-top-color: #ffffff; border-top-style: solid; border-bottom-width: 1px; border-bottom-color: #bac2cc; border-bottom-style: solid;">
																						<?php 
																						// Example of php: previous newsletters
																						if ( function_exists('alo_easymail_print_archive') ) echo alo_easymail_print_archive(); 
																						?>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																</tr>
															</tbody>
										<!-- /article--></table>
													</td><!--/content-->
												</tr>
												<tr>
													<td colspan="2" valign="middle" height="50" bgcolor="#e7eaee" style="vertical-align:middle; border-width: 1px; border-style: solid; border-color: #b6bec9; text-align: center;">
														[READ-ONLINE]
														[USER-UNSUBSCRIBE]
														<p>[SITE-LINK]</p>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table><!--/container-->
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>
