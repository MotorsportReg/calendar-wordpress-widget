<?php
/*
Plugin Name: MotorsportReg.com Calendar Sidebar Widget
Description: An add-on to MSR's Calendar plugin
Version: 1.0
Author: Katja Stokley
*/

class wp_msr_calendar extends WP_Widget {

	// constructor
	function wp_msr_calendar() {
		parent::WP_Widget(false, $name = __('MSR Calendar', 'wp_widget_plugin') );
	}

	// widget form creation
	function form($instance) {

	// Check values
	if( $instance) {
	     $title = esc_attr($instance['title']);
	} else {
	     $title = '';
	}
	?>

	<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>

	<?php
	}

	// update widget
	function update($new_instance, $old_instance) {
	      $instance = $old_instance;
	      // Fields
	      $instance['title'] = strip_tags($new_instance['title']);
	     return $instance;
	}

	// display widget
	function widget($args, $instance) {
	   extract( $args );
	   // these are the widget options
	   $title = apply_filters('widget_title', $instance['title']);
	   $text = $instance['text'];
	   $textarea = $instance['textarea'];
	   echo $before_widget;
	   // Display the widget
	   echo '<div class="widget-text wp_widget_plugin_box">';

	   // Check if title is set
	   if ( $title ) {
	      echo $before_title . $title . $after_title;
	   }

	   // Display calendar
		global $wpdb, $wp_version;  
		$req_url=get_option('msr_calendar_url');
		$cache_update_after_time=get_option('msr_calendar_cache_time') * 60 * 60;

		$data=request_cache($req_url,$cache_update_after_time);

		echo msr_calendar_sidebar($data);

	   echo '</div>';
	   echo $after_widget;
	}

}

function msr_calendar_sidebar($xml)
{
	$field_eventname=get_option('msr_calendar_display_field_eventname');
	$field_organization=get_option('msr_calendar_display_field_organization');
	$field_venue=get_option('msr_calendar_display_field_venue');
	$field_venuecity=get_option('msr_calendar_display_field_venuecity');
	$field_eventtype=get_option('msr_calendar_display_field_eventtype');
	$field_eventdate=get_option('msr_calendar_display_field_eventdate');   
	$title=get_option('msr_calendar_title');
	$data1 = json_decode($xml, true);
	$event_tot=$data1["recordset"]["total"];
	if($event_tot == 1){
		$events= $data1["events"]; 
	}else{
		$events= $data1["events"]["event"]; 
	}

	$out .= '<div class="msrcalendar">';
	$show=false;

	if($field_eventdate){
		$show=true;
	}

	if($field_eventname){
		$show=true;
	}

	if($field_organization){
		$show=true;
	}

	if($field_venue){
		$show=true;
	}

	if($field_venuecity){
		$show=true;
	}

	if($field_eventtype){
		$show=true;
	}

	if(count($events)){

	foreach($events as $event)
	{

		if($field_eventname)
			$out .='<h3>'.$event[name].'</h3>';

		$out .='<p>';
		
		if($field_eventdate){
			$start=$event[start];
			$end=$event[end];
			if(!$end)
				$out .= date_display_formate($start).'<br/>';
			else
				$out .= date_display_formate($start,$end).'<br/>';
		}


		if($field_organization)
			$out .= $event[organization][name].'<br/>';

		if($field_venue)
			$out .= $event[venue][name].'<br/>';

		if($field_venuecity)
			$out .= $event[venue][city].', '.$event[venue][region].'<br/>';

		if($field_eventtype)
			$out .= $event[type].'<br/>';

		$r_start=$event[registration][start];
		$r_end=$event[registration][end];
		$flag=registr_display_formate($r_start,$r_end);
		if($show){
			if($flag)
				$out .= '<a href="'.$event[detailuri].'" class="imglink"><img src="' . plugins_url('calendar/images/register.gif', __FILE__) . '" height="17" width="85" alt="Register now on MotorsportReg.com" /></a><br/>';
			else
				$out .='<a href="'.$event[detailuri].'" class="txtlink">More Details</a><br/>';
		}

		$out .='</p>';
		}
		}else{
		$out .='<p>No upcoming events.</p>';
	}

	// please do not remove the link to motorsportreg.com - the only requirement for using 
	// our otherwise-free plugin is to retain an unedited link back to our site, thanks!
	$out .= '<p style="font-size: 85%;">Use MotorsportReg.com for <a href="http://www.motorsportreg.com/index.cfm/event/event-management">online driving event registration</a>. Register for thousands of <a href="http://www.motorsportreg.com/calendar/">autocross, HPDE, race &amp; social events</a>.</p></div>';

	return $out;
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("wp_msr_calendar");'));