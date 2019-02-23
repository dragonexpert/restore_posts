<?php
/**
 * Created by PhpStorm.
 * User: Latios
 * Date: 2/16/2019
 * Time: 8:24 AM
 */
if(!defined("IN_MYBB"))
{
    die("Direct access not allowed.");
}

function restore_posts_templates_install()
{
    global $db;
    $new_template['modcp_nav_restore_posts'] = '<tr><td class="trow1 smalltext"><a href="modcp.php?action=restore_posts" class="modcp_nav_item modcp_nav_restore_posts">Restore Posts</a></td></tr>';

    $new_template['modcp_restore_posts_post'] = '			<tr>
				<td class="{$altbg}"><a href="{$post[\'postlink\']}#pid{$post[\'pid\']}">{$post[\'subject\']}</a></td>
				<td class="{$altbg}" align="center">{$profile_link}</td>
				<td align="center" class="{$altbg}">{$postdate}</td>
			</tr>
			<tr>
				<td class="{$altbg}" colspan="3">
					<div class="modqueue_message">
						<div class="float_right modqueue_controls">
							<label class="label_radio_ignore"><input type="radio" class="radio radio_ignore" name="posts[{$post[\'pid\']}]" value="ignore" checked="checked" /> {$lang->ignore}</label>
							<label class="label_radio_delete"><input type="radio" class="radio radio_delete" name="posts[{$post[\'pid\']}]" value="delete" /> {$lang->delete}</label>
							<label class="label_radio_approve"><input type="radio" class="radio radio_approve" name="posts[{$post[\'pid\']}]" value="restore" /> {$lang->restore_posts_restore}</label>
						</div>
						<div class="modqueue_meta">
							{$forum}{$thread}
						</div>
						{$post[\'message\']}
					</div>
				</td>
			</tr>';

    $new_template['modcp_restore_posts_post_empty'] = '<tr>
		<td class="trow1" align="center" colspan="3">{$lang->restore_posts_posts_queue_empty}</td>
</tr>';

    $new_template['modcp_restore_posts_posts'] = '<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->restore_posts_posts}</title>
{$headerinclude}
</head>
<body>
{$header}
<form action="modcp.php" method="post">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="action" value="restore_posts" />
<table width="100%" border="0" align="center">
<tr>
{$modcp_nav}
<td valign="top">
	<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
			<td class="thead" colspan="3">
				<div class="float_right">
					<a href="modcp.php?action=restore_posts&amp;type=threads">{$lang->restore_posts_threads}</a>{$navsep}
					<strong>{$lang->posts}</strong>
				</div>
				<strong>{$lang->restore_posts_posts}</strong>
			</td>
		</tr>
		<tr>
			<td class="tcat" width="50%"><span class="smalltext"><strong><a href="modcp.php?action=restore_posts&amp;type=posts&amp;order_by=subject">{$lang->subject}</a></strong></span></td>
			<td class="tcat" align="center" width="25%"><span class="smalltext"><strong><a href="modcp.php?action=restore_posts&amp;type=posts&amp;order_by=author">{$lang->author}</a></strong></span></td>
			<td class="tcat" align="center" width="25%"><span class="smalltext"><strong><a href="modcp.php?action=restore_posts&amp;type=posts">{$lang->date}</a></strong></span></td>
		</tr>
		{$posts}
		</table>
{$multipage}
<br />
<div align="center"><input type="submit" class="button" name="reportsubmit" value="{$lang->perform_actions}" /></div>
</td>
</tr>
</table>
</form>
{$footer}
</body>
</html>';

    $new_template['modcp_restore_posts_posts_empty'] = '<tr>
		<td class="trow1" align="center" colspan="3">{$lang->restore_posts_posts_queue_empty}</td>
</tr>';

    $new_template['modcp_restore_posts_threads'] = '<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->restore_posts_restore_threads}</title>
{$headerinclude}
</head>
<body>
{$header}
<form action="modcp.php" method="post">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="action" value="restore_posts" />
<table width="100%" border="0" align="center">
<tr>
{$modcp_nav}
<td valign="top">
	<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
			<td class="thead" colspan="3">
				<div class="float_right">
					<strong>{$lang->restore_posts_threads}</strong>{$navsep}
					<a href="modcp.php?action=restore_posts&amp;type=posts">{$lang->restore_posts_post_link}</a>
				</div>
				<strong>{$lang->restore_posts_restore_threads}</strong>
			</td>
		</tr>
		<tr>
			<td class="tcat" width="50%"><span class="smalltext"><strong><a href="modcp.php?action=restore_posts&amp;type=threads&amp;order_by=subject">{$lang->subject}</a></strong></span></td>
			<td class="tcat" align="center" width="25%"><span class="smalltext"><strong><a href="modcp.php?action=restore_posts&amp;type=threads&amp;order_by=author">{$lang->author}</a></strong></span></td>
			<td class="tcat" align="center" width="25%"><span class="smalltext"><strong><a href="modcp.php?action=restore_posts&amp;type=threads">{$lang->date}</a></strong></span></td>
		</tr>
		{$threads}
		</table>
{$multipage}
<br />
<div align="center"><input type="submit" class="button" name="reportsubmit" value="{$lang->perform_actions}" /></div>
</td>
</tr>
</table>
</form>
{$footer}
</body>
</html>';

    $new_template['modcp_restore_posts_threads_empty'] = '<tr>
		<td class="trow1" align="center" colspan="3">{$lang->restore_posts_threads_queue_empty}</td>
</tr>';

    $new_template['modcp_restore_posts_threads_thread'] = '			<tr>
				<td class="{$altbg}"><a href="{$thread[\'threadlink\']}">{$thread[\'subject\']}</a></td>
				<td class="{$altbg}" align="center">{$profile_link}</td>
				<td align="center" class="{$altbg}">{$threaddate}</td>
			</tr>
			<tr>
				<td class="{$altbg}" colspan="3">
					<div class="modqueue_message">
						<div class="float_right modqueue_controls">
							<label class="label_radio_ignore"><input type="radio" class="radio radio_ignore" name="threads[{$thread[\'tid\']}]" value="ignore" checked="checked" /> {$lang->ignore}</label>
							<label class="label_radio_delete"><input type="radio" class="radio radio_delete" name="threads[{$thread[\'tid\']}]" value="delete" /> {$lang->delete}</label>
							<label class="label_radio_approve"><input type="radio" class="radio radio_approve" name="threads[{$thread[\'tid\']}]" value="restore" /> {$lang->restore_posts_restore}</label>
						</div>
						<div class="modqueue_meta">
							{$forum}
						</div>
						{$thread[\'postmessage\']}
					</div>
				</td>
			</tr>';

    // Now go through each of the themes
    $themequery = $db->simple_select("themes", "*");
    $sids = array();
    $first = true;
    while($theme = $db->fetch_array($themequery))
    {
        $properties = unserialize($theme['properties']);
        $sid = $properties['templateset'];
        if(!in_array($sid, $sids))
        {
            array_push($sids, $sid);
            foreach ($new_template as $title => $template)
            {
                $my_template = array(
                    'title' => $db->escape_string($title),
                    'template' => $db->escape_string($template),
                    'sid' => $sid,
                    'version' => '1800',
                    'dateline' => TIME_NOW);
                $db->insert_query('templates', $my_template);
                if($first)
                {
                    $my_new_template = array(
                        "title" => $db->escape_string($title),
                        "template" => $db->escape_string($template),
                        "sid" => -2,
                        "version" => "1814",
                        "dateline" => TIME_NOW
                    );
                    $db->insert_query("templates", $my_new_template);
                }
            }
            $first = false;
        }
    }

    require_once MYBB_ROOT . "inc/adminfunctions_templates.php";
    find_replace_templatesets('modcp_nav_forums_posts', "#" . preg_quote('{$nav_modqueue}') . "#i", "{\$nav_modqueue}\n{\$modcp_nav_restore_posts}");
}

function restore_posts_templates_uninstall()
{
    global $db;
    $template_array = array("modcp_nav_restore_posts", "modcp_restore_posts_post", "modcp_restore_posts_post_empty", "modcp_restore_posts_posts", "modcp_restore_posts_posts_empty",
        "modcp_restore_posts_threads", "modcp_restore_posts_threads_empty", "modcp_restore_posts_threads_thread");

    $string = "";
    $comma = "";
    foreach($template_array as $name)
    {
        $string .= $comma . "'" . $name . "'";
        $comma = ",";
    }
    $db->delete_query("templates", "title IN(" . $string . ")");

    require_once MYBB_ROOT . "inc/adminfunctions_templates.php";
    find_replace_templatesets('modcp_nav_forums_posts', "#" . preg_quote('{$modcp_nav_restore_posts}') . "#i", "");
}
