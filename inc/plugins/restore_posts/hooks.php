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

$plugins->add_hook("modcp_nav", "restore_posts_modcp_nav");
$plugins->add_hook("modcp_start", "restore_posts_modcp");
$plugins->add_hook("global_start", "restore_posts_global_start");

function restore_posts_modcp_nav()
{
    global $db, $mybb, $templates, $modcp_nav_restore_posts, $lang, $restore;
    $lang->load("restore_posts");
    $restore = 0;
    if($mybb->usergroup['issupermod'] != 1)
    {
        $query = $db->simple_select("moderators", "*", "(id='{$mybb->user['uid']}' AND isgroup = '0') OR (id IN ({$mybb->usergroup['all_usergroups']}) AND isgroup = '1')");
        while ($moderator = $db->fetch_array($query))
        {
            if ($moderator['canrestorethreads'])
            {
                $restore = 1;
                break;
            }
        }
    }
    else
    {
        $restore = 1;
    }
    if($restore == 1)
    {
        eval("\$modcp_nav_restore_posts =\"".$templates->get("modcp_nav_restore_posts")."\";");
    }
}

function restore_posts_modcp()
{
    global $db, $mybb, $restore, $lang, $plugins, $parser, $templates, $forum_cache;
    global $headerinclude, $header, $theme, $modcp_nav, $footer;
    if($mybb->get_input("action") == "restore_posts")
    {
        if($restore == 0)
        {
            error_no_permission();
        }
        $lang->load("moderation");
        if ($mybb->request_method == "post")
        {
            verify_post_check($mybb->get_input('my_post_key'));
            require_once MYBB_ROOT . "inc/class_moderation.php";
            $moderation = new Moderation;
            $plugins->run_hooks("modcp_do_restore_posts_start");
            $mybb->input['threads'] = $mybb->get_input("threads", MyBB::INPUT_ARRAY);
            $mybb->input['posts'] = $mybb->get_input("posts", MyBB::INPUT_ARRAY);
            if (!empty($mybb->input['threads']))
            {
                $threads = array_map("intval", array_keys($mybb->input['threads']));
                $threads_to_delete = $threads_to_restore = array();
                // Fetch the threads relevant.
                $query = $db->simple_select("threads", "*", "tid IN(" . implode(",", $threads) . ")");
                while ($thread = $db->fetch_array($query))
                {
                    if (!isset($mybb->input['threads'][$thread['tid']]))
                    {
                        continue;
                    }
                    $action = $mybb->input['threads'][$thread['tid']];
                    if ($action == "restore")
                    {
                        $threads_to_restore[] = $thread['tid'];
                    }
                    else if ($action == "delete")
                    {
                        $threads_to_delete[] = $thread['tid'];
                    }
                }
                if (!empty($threads_to_restore))
                {
                    $moderation->restore_threads($threads_to_restore);
                    log_moderator_action(array("tids" => $threads_to_restore), $lang->multi_restored_threads);
                }
                if (!empty($threads_to_delete))
                {
                    foreach ($threads_to_delete as $tid)
                    {
                        $moderation->delete_thread($tid);
                    }
                    log_moderator_action(array("tids" => $threads_to_delete), $lang->multi_deleted_threads);
                }
                $plugins->run_hooks("modcp_do_restore_threads_end");
                redirect("modcp.php?action=restore_posts", $lang->restore_posts_redirect);
            } // End threads
            if(!empty($mybb->input['posts']))
            {
                $posts = array_map("intval", array_keys($mybb->input['posts']));
                $posts_to_delete = $posts_to_restore = array();
                // Fetch relevant posts
                $query = $db->simple_select("posts", "*", "pid IN(" . implode(",", $posts) . ")");
                while($post = $db->fetch_array($query))
                {
                    if(isset($mybb->input['posts'][$post['pid']]))
                    {
                        continue;
                    }
                    $action = $mybb->input['posts'][$post['pid']];
                    if($action == "restore")
                    {
                        $posts_to_restore[] = $post['pid'];
                    }
                    if($action == "delete")
                    {
                        $posts_to_delete[] = $post['pid'];
                    }
                }
                if(!empty($posts_to_restore))
                {
                    $moderation->restore_posts($posts_to_restore);
                    log_moderator_action(array("pids" => $posts_to_restore), $lang->restore_posts_restore_posts);
                }
                if(!empty($posts_to_delete))
                {
                    foreach($posts_to_delete as $pid)
                    {
                        $moderation->delete_post($pid);
                    }
                    log_moderator_action(array("pids" => $posts_to_delete), $lang->sprint_f("deleted_selective_posts", $posts_to_delete));
                }
                $plugins->run_hooks("modcp_do_restore_posts_end");
                redirect("modcp.php?action=restore_posts", $lang->restore_posts_redirect);
            } // End posts
        } // End post method
        if(!$mybb->input['type'])
        {
            $type = "threads";
            $mybb->input['type'] = "threads";
        }
        else
        {
            $type = $mybb->get_input("type");
        }
        $plugins->run_hooks("modcp_restore_posts");
        if($type == "threads")
        {
            $fidonly = "";
            if($mybb->usergroup['issupermod'] != 1)
            {
                $query = $db->simple_select("moderators", "*", "(id='{$mybb->user['uid']}' AND isgroup = '0') OR (id IN ({$mybb->usergroup['all_usergroups']}) AND isgroup = '1')");
                while ($moderator = $db->fetch_array($query))
                {
                    if ($moderator['canrestorethreads'])
                    {
                        $fids[] = $moderator['fid'];
                    }
                }
                $fidonly = " AND t.fid IN(" . implode(",", $fids);
            }
            if(!isset($mybb->input['order_by']))
            {
                $order = "dateline";
            }
            else
            {
                $order = $mybb->get_input("order_by");
            }
            switch($order)
            {
                case "dateline":
                    $order_by = "t.dateline DESC";
                    break;
                case "subject":
                    $order_by = "t.subject ASC";
                    break;
                case "author":
                    $order_by = "u.username ASC";
                    break;
                default:
                    $order_by = "t.dateline DESC";
                    $order = "dateline";
                    break;
            }
            $query = $db->simple_select("threads t", "COUNT(t.tid) AS deletedthreads", "visible='-1' " . $fidonly);
            $deletedthreads = $db->fetch_field($query, "deletedthreads");
            // Figure out if we need to display multiple pages.
            if($mybb->get_input('page') != "last")
            {
                $page = $mybb->get_input('page', MyBB::INPUT_INT);
            }
            $perpage = $mybb->settings['threadsperpage'];
            $pages = $deletedthreads / $perpage;
            $pages = ceil($pages);
            if($mybb->get_input('page') == "last")
            {
                $page = $pages;
            }
            if($page > $pages || $page <= 0)
            {
                $page = 1;
            }
            if($page)
            {
                $start = ($page-1) * $perpage;
            }
            else
            {
                $start = 0;
                $page = 1;
            }
            $multipage = multipage($deletedthreads, $perpage, $page, "modcp.php?action=restore_posts&type=threads&order_by=" . $order);

            $threadquery = $db->query("SELECT t.*, t.username AS threadusername, p.*, p.message AS postmessage, u.uid, u.username AS username FROM " . TABLE_PREFIX . "threads t
            LEFT JOIN " . TABLE_PREFIX . "posts p ON(t.firstpost=p.pid)
            LEFT JOIN " . TABLE_PREFIX . "users u ON(t.uid=u.uid)
            WHERE t.visible=-1 " . $fidonly . "
            ORDER BY " . $order_by
                . " LIMIT " . $start . ", " . $perpage);

            $threads = "";
            while($thread = $db->fetch_array($threadquery))
            {
                $altbg = alt_trow();
                $thread['subject'] = $parser->parse_badwords($thread['subject']);
                $thread['threadlink'] = get_thread_link($thread['tid']);
                $forum_link = get_forum_link($thread['fid']);
                $forum_name = $forum_cache[$thread['fid']]['name'];
                $threaddate = my_date('relative', $thread['dateline']);
                if($thread['username'] == "")
                {
                    if($thread['threadusername'] != "")
                    {
                        $thread['threadusername'] = htmlspecialchars_uni($thread['threadusername']);
                        $profile_link = $thread['threadusername'];
                    }
                    else
                    {
                        $profile_link = $lang->guest;
                    }
                }
                else
                {
                    $thread['username'] = htmlspecialchars_uni($thread['username']);
                    $profile_link = build_profile_link($thread['username'], $thread['uid']);
                }
                $thread['postmessage'] = nl2br(htmlspecialchars_uni($thread['postmessage']));
                eval("\$forum = \"".$templates->get("modcp_modqueue_link_forum")."\";");
                eval("\$threads .= \"".$templates->get("modcp_restore_posts_threads_thread")."\";");
            }
            if(!$threads && $mybb->input['type'] == "threads")
            {
                eval("\$threads = \"".$templates->get("modcp_restore_posts_threads_empty")."\";");
            }
            if($threads)
            {
                add_breadcrumb($lang->modcp_nav_restore_posts, "modcp.php?action=restore_threads&amp;type=threads");
                $plugins->run_hooks("modcp_restore_threads_end");
                $navsep = " | ";
                eval("\$threadqueue = \"".$templates->get("modcp_restore_posts_threads")."\";");
                output_page($threadqueue);
            }
        }
        if($type == "posts")
        {
            $fidonly = "";
            if($mybb->usergroup['issupermod'] != 1)
            {
                $query = $db->simple_select("moderators", "*", "(id='{$mybb->user['uid']}' AND isgroup = '0') OR (id IN ({$mybb->usergroup['all_usergroups']}) AND isgroup = '1')");
                while ($moderator = $db->fetch_array($query))
                {
                    if ($moderator['canrestorethreads'])
                    {
                        $fids[] = $moderator['fid'];
                    }
                }
                $fidonly = " AND t.fid IN(" . implode(",", $fids);
            }

            if(!isset($mybb->input['order_by']))
            {
                $order = "dateline";
            }
            else
            {
                $order = $mybb->get_input("order_by");
            }
            switch($order)
            {
                case "dateline":
                    $order_by = "p.dateline DESC";
                    break;
                case "subject":
                    $order_by = "t.subject ASC";
                    break;
                case "author":
                    $order_by = "u.username ASC";
                    break;
                default:
                    $order_by = "p.dateline DESC";
                    $order = "dateline";
                    break;
            }

            $query = $db->query("
			SELECT COUNT(pid) AS deletedposts
			FROM  ".TABLE_PREFIX."posts p
			LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
			WHERE p.visible='-1' " . $fidonly . " AND t.firstpost != p.pid
		    ");
            $deleted_posts = $db->fetch_field($query, "deletedposts");

            if($mybb->get_input('page') != "last")
            {
                $page = $mybb->get_input('page', MyBB::INPUT_INT);
            }
            $perpage = $mybb->settings['postsperpage'];
            $pages = $deleted_posts / $perpage;
            $pages = ceil($pages);
            if($mybb->get_input('page') == "last")
            {
                $page = $pages;
            }
            if($page > $pages || $page <= 0)
            {
                $page = 1;
            }
            if($page)
            {
                $start = ($page-1) * $perpage;
            }
            else
            {
                $start = 0;
                $page = 1;
            }
            $multipage = multipage($deleted_posts, $perpage, $page, "modcp.php?action=restore_posts&amp;type=posts&amp;order_by=" . $order);

            $postquery = $db->query("SELECT p.pid, p.subject, p.message, p.username AS postusername, t.subject AS threadsubject, t.tid, u.username, p.uid, t.fid, p.dateline
			FROM  ".TABLE_PREFIX."posts p
			LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
			LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
			WHERE p.visible=-1 " . $fidonly . " AND t.firstpost != p.pid
			ORDER BY " . $order_by .
                " LIMIT " . $start . ", " . $perpage);
            $posts = '';
            while($post = $db->fetch_array($postquery))
            {
                $altbg = alt_trow();
                $post['threadsubject'] = htmlspecialchars_uni($parser->parse_badwords($post['threadsubject']));
                $post['subject'] = htmlspecialchars_uni($parser->parse_badwords($post['subject']));
                $post['threadlink'] = get_thread_link($post['tid']);
                $post['postlink'] = get_post_link($post['pid'], $post['tid']);
                $forum_link = get_forum_link($post['fid']);
                $forum_name = $forum_cache[$post['fid']]['name'];
                $postdate = my_date('relative', $post['dateline']);
                if($post['username'] == "")
                {
                    if($post['postusername'] != "")
                    {
                        $post['postusername'] = htmlspecialchars_uni($post['postusername']);
                        $profile_link = $post['postusername'];
                    }
                    else
                    {
                        $profile_link = $lang->guest;
                    }
                }
                else
                {
                    $post['username'] = htmlspecialchars_uni($post['username']);
                    $profile_link = build_profile_link($post['username'], $post['uid']);
                }
                eval("\$thread = \"".$templates->get("modcp_modqueue_link_thread")."\";");
                eval("\$forum = \"".$templates->get("modcp_modqueue_link_forum")."\";");
                $post['message'] = nl2br(htmlspecialchars_uni($post['message']));
                eval("\$posts .= \"".$templates->get("modcp_restore_posts_post")."\";");
            }
            if(!$posts && $mybb->input['type'] == "posts")
            {
                eval("\$posts = \"".$templates->get("modcp_restore_posts_posts_empty")."\";");
            }
            if($posts)
            {
                add_breadcrumb($lang->mcp_nav_modqueue_posts, "modcp.php?action=restore_posts&amp;type=posts");
                $navsep = " | ";
                $plugins->run_hooks("modcp_restore_posts_end");
                eval("\$postqueue = \"".$templates->get("modcp_restore_posts_posts")."\";");
                output_page($postqueue);
            }
        }
    }
}

function restore_posts_global_start()
{
    global $templatelist;
    if(THIS_SCRIPT == "modcp.php")
    {
        $templatelist .= ",modcp_restore_posts_threads,modcp_restore_posts_masscontrols,modcp_restore_posts_threads_thread,modcp_restore_posts_posts,modcp_restore_posts_post";
        $templatelist .= ",modcp_restore_posts_threads_empty,modcp_restore_posts_posts_empty,modcp_nav_restore_posts, modcp_restore_posts_posts_empty, modcp_restore_posts_posts";
    }
}
