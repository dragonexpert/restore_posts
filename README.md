# Restore Posts
Adds the ability to restore posts and threads to the Mod CP of your MyBB Forum.  Soft deleted threads and posts are able to be sorted by date, subject, and author.  Re: is ignored for subjects for sorting purposes.

##Database Changes
None

##Template Changes
-8 New templates
- Modifies modcp_nav_forums_posts

##Template Descriptions
- modcp_nav_restore_posts : Used for the Mod CP nav menu
- modcp_restore_posts_post : An individual post
- modcp_restore_posts_post_empty : No soft deleted posts
- modcp_restore_posts_posts : Soft deleted posts page
- modcp_restore_posts_posts_empty : No soft deleted posts
- modcp_restore_posts_threads : Soft deleted threads page
- modcp_restore_posts_threads_empty : No soft deleted threads
- modcp_restore_posts_threads_thread : An individual thread

##Settings
None

##Hooks
- modcp_do_restore_posts_start : Start of a post request.  Useful if you are using additional content that can be soft deleted such as profile comments.
- modcp_do_restore_threads_end : End of a post request for threads.
- modcp_do_restore_posts_end : End of a post request for posts.
- modcp_restore_posts : Used if it is not a post request and you wish to show additional soft deleted content such as profile comments.
- modcp_restore_threads_end : End of showing soft deleted threads.
- modcp_restore_posts_end : End of showing soft deleted posts

##Languages
- English
