<?php
/**
 * Template Name: Admin Portal
 *
 * If the user has selected a static page for their homepage, this is what will
 * appear.
 * Learn more: https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */
//require_once("phpGrid_Enterprise/conf.php");
get_header(); 
$error_message = '';
if ( isset( $_POST['checkbox_id_action'] ) && is_array( $_POST['checkbox_id_action'] ) && count( $_POST['checkbox_id_action'] ) ) {

	//echo '<pre>';
	//print_r($_POST);die;
	$error_message = t4c_process_admin_grid_action( $_POST );
}
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php // Show the selected frontpage content.
		if ( have_posts() ) :
			while ( have_posts() ) : the_post();?>
				<header class="entry-header" style="text-align:center;">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                <?php twentyseventeen_edit_link( get_the_ID() ); ?>
               </header><!-- .entry-header -->
			<?php endwhile;
		endif; ?>
        <?php if( isset($error_message) && !empty($error_message) )
			  {
				  echo "<div class='error_message'>$error_message</div>";
			  }       ?>
        <div class="tab">
          <button class="tablinks active" onclick="openCity(event, 'Team')">Team</button>
          <button class="tablinks" onclick="openCity(event, 'Organization')">Organization</button>
          <button class="tablinks" onclick="openCity(event, 'Entity')">Entity</button>
          <button class="tablinks" onclick="openCity(event, 'Indiv')">Indiv Fund</button>
        </div>
        
        <div id="Team" class="tabcontent" style="display:block;">
          <h3>Team Page</h3>
           <?php $dg = new C_DataGrid("SELECT p.ID as pid, p.post_title as Title, te.meta_value as Shortname, p.post_status as Status FROM wpui_posts AS p 
		  LEFT JOIN wpui_postmeta te ON p.ID = te.post_id
		  where te.meta_key =  'short_name' and p.post_type = 'team' and post_status in('pending','publish') ","ID","team"); 
		  $dg -> set_col_title("pid", "Action");
		  $dg -> set_col_property("pid", array("sortable"=> false));
		  $dg -> set_col_width("pid",60);
		  $dg->set_col_align('pid', 'center');
		  $dg -> set_col_edittype("pid", "checkbox");
		  $dg->display();?>
        </div>
        
        <div id="Organization" class="tabcontent">
          <h3>Organization</h3>
          <form action="" method="post">
          <div class="admin_grid_select">
            <select id="admin_grid_select_action" name="admin_grid_select_action">
                <option value="Select One" selected="">Select One</option>
                <option value="approve">Approve</option>               
                <option value="unapprove">Unapprove</option>
            </select>
            <input hidden="hidden" name='ids[]' id="selected-rows" aria-hidden="true">    
        </div>
        <div class="admin_grid_submit">
        <input id="admin_grid_action_save_button" type="submit" value="Send" class="contributor_listing_save_button" name="admin_grid_action_save_button">
        </div>
          <?php $dg = new C_DataGrid("SELECT p.ID as pid, p.post_title as Title, te.meta_value as Teamname, p.post_status as Status FROM wpui_posts AS p 
		  LEFT JOIN wpui_postmeta te ON p.ID = te.post_id
		  where te.meta_key =  'team_name' and p.post_type = 'organization' and post_status in('pending','publish') ","ID","organization"); 
		  $dg -> set_col_title("pid", "Action");
		  $dg -> set_col_property("pid", array("sortable"=> false));
		  $dg -> set_col_width("pid",60);
		  $dg->set_col_align('pid', 'center');
		  $dg -> set_col_edittype("pid", "checkbox");
		  $dg->display();?>
          </form>
        </div>
        
        <div id="Entity" class="tabcontent">
          <h3>Entity</h3>
          <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p> 
        </div>
        
        <div id="Indiv" class="tabcontent">
          <h3>Individual</h3>
          <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
        </div>
		<?php
		// Get each of our panels and show the post data.
		if ( 0 !== twentyseventeen_panel_count() || is_customize_preview() ) : // If we have pages to show.

			/**
			 * Filter number of front page sections in Twenty Seventeen.
			 *
			 * @since Twenty Seventeen 1.0
			 *
			 * @param int $num_sections Number of front page sections.
			 */
			$num_sections = apply_filters( 'twentyseventeen_front_page_sections', 4 );
			global $twentyseventeencounter;

			// Create a setting and control for each of the sections available in the theme.
			for ( $i = 1; $i < ( 1 + $num_sections ); $i++ ) {
				$twentyseventeencounter = $i;
				twentyseventeen_front_page_section( null, $i );
			}

	endif; // The if ( 0 !== twentyseventeen_panel_count() ) ends here. ?>

	</main><!-- #main -->
</div><!-- #primary -->
<style>
body {font-family: Arial;}

/* Style the tab */
.tab {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
	width:80%;
	margin:auto;
}

/* Style the buttons inside the tab */
.tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
    font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
    background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
    background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
    display: none;
	width:80%;
	margin:auto;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
}
.admin_grid_select{ float:left;}
</style>
<script>
function openCity(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}
</script>
<?php get_footer();
