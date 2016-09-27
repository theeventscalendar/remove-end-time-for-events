<?php
/**
 * Plugin Name: The Events Calendar Extension: Remove End Time for Events
 * Description: Removes the end time for Events in all event views.
 * Version: 1.0.0
 * Author: Modern Tribe, Inc.
 * Author URI: http://m.tri.be/1971
 * License: GPLv2 or later
 */
 
defined( 'WPINC' ) or die;

class Tribe__Extension__Remove_End_Time_for_Events {

    /**
     * The semantic version number of this extension; should always match the plugin header.
     */
    const VERSION = '1.0.0';

    /**
     * Each plugin required by this extension
     *
     * @var array Plugins are listed in 'main class' => 'minimum version #' format
     */
    public $plugins_required = array(
        'Tribe__Events__Main' => '4.2'
    );

    /**
     * The constructor; delays initializing the extension until all other plugins are loaded.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ), 100 );
    }

    /**
     * Extension hooks and initialization; exits if the extension is not authorized by Tribe Common to run.
     */
    public function init() {

        // Exit early if our framework is saying this extension should not run.
        if ( ! function_exists( 'tribe_register_plugin' ) || ! tribe_register_plugin( __FILE__, __CLASS__, self::VERSION, $this->plugins_required ) ) {
            return;
        }

        add_filter( 'tribe_events_event_schedule_details_formatting', array( $this, 'remove_end_time_single' ) );
        add_filter( 'tribe_events_template_data_array', array( $this, 'remove_end_time_tooltips' ), 10, 2 );
        add_filter( 'tribe_events_event_schedule_details_inner', array( $this, 'remove_endtime_from_multiday_events' ), 10, 2 );
    }

    /**
     * Hides the end time on list, map, photo, and single event views. 
     *
     * @param array $formatting_details
     * @return array
     */
    public function remove_end_time_single( $formatting_details ) {
        
        $formatting_details['show_end_time'] = 0;
        
        return $formatting_details;
    }
    
    /**
     * Hides the end time in week and month view tooltips.
     *
     * @param array $json_array
     * @param object $event
     * @return array
     */
    public function remove_end_time_tooltips( $json_array, $event ) {
        
        $json_array['endTime'] = '';
        
        return $json_array;
    }
    
    /**
     * Hides the end time on multi-day events.
     *
     * @param string $inner
     * @param object $event
     * @return string
     */
    public function remove_endtime_from_multiday_events( $inner, $event ) {
    
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
}

new Tribe__Extension__Remove_End_Time_for_Events();
