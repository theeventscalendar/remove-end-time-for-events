<?php
/**
 * Plugin Name: The Events Calendar — Remove End Time for Events
 * Description: Removes the end time for Events in all event views.
 * Version: 1.0.0
 * Author: Modern Tribe, Inc.
 * Author URI: http://m.tri.be/1x
 * License: GPLv2 or later
 */
 
defined( 'WPINC' ) or die;

/**
 * Hides the end time on list, map, photo, and single event views. 
 *
 * @param array $formatting_details
 * @return array
 */
function tribe_remove_end_time_single( $formatting_details ) {
	
	$formatting_details['show_end_time'] = 0;
	
	return $formatting_details;
}

add_filter( 'tribe_events_event_schedule_details_formatting', 'tribe_remove_end_time_single' );

/**
 * Hides the end time in week and month view tooltips.
 *
 * @param array $json_array
 * @param object $event
 * @return array
 */
function tribe_remove_end_time_tooltips( $json_array, $event ) {
	
	$json_array['endTime'] = '';
	
	return $json_array;
}

add_filter( 'tribe_events_template_data_array', 'tribe_remove_end_time_tooltips', 10, 2 );

/**
 * Hides the end time on multi-day events.
 *
 * @param string $inner
 * @param object $event
 * @return string
 */
function tribe_remove_endtime_from_multiday_events( $inner, $event ) {

	if ( ! tribe_event_is_multiday( $event ) ) {
		return $inner;
	}

	if ( tribe_event_is_all_day( $event ) ) {
		return $inner;
	}
	
	$format                 = tribe_get_date_format( true );
	$time_format            = get_option( 'time_format' );
	$format2ndday           = apply_filters( 'tribe_format_second_date_in_range', $format, $event );
	$datetime_separator     = tribe_get_option( 'dateTimeSeparator', ' @ ' );
	$time_range_separator   = tribe_get_option( 'timeRangeSeparator', ' - ' );
	$microformatStartFormat = tribe_get_start_date( $event, false, 'Y-m-dTh:i' );
	$microformatEndFormat   = tribe_get_end_date( $event, false, 'Y-m-dTh:i' );

	$inner = '<span class="date-start dtstart">';
		$inner .= tribe_get_start_date( $event, false, $format ) . $datetime_separator . tribe_get_start_date( $event, false, $time_format );
		$inner .= '<span class="value-title" title="' . $microformatStartFormat . '"></span>';
		$inner .= '</span>' . $time_range_separator;
		$inner .= '<span class="date-end dtend">';
		$inner .= tribe_get_end_date( $event, false, $format2ndday );
		$inner .= '<span class="value-title" title="' . $microformatEndFormat . '"></span>';
	$inner .= '</span>';

	return $inner;
}

add_filter( 'tribe_events_event_schedule_details_inner', 'tribe_remove_endtime_from_multiday_events', 10, 2 );
