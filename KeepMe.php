	public function __call($method, $args) {
		$prefix = substr($method, 0, 3);
		if (($prefix == 'get') || ($prefix == 'set')) {
			$key = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', substr($method, 3))), '_');
			if (array_key_exists($key, $this->cache)) {
				if ($prefix == 'get') {
					// there should be no arguments to get
					if (count($args))
						trigger_error("Method {$method} takes no parameters; " . count($args) . " given.", E_USER_ERROR);
					return $this->$key;
				} else {
					// there should only be one argument to set
					if (count($args) != 1)
						trigger_error("Method {$method} takes one parameter; " . count($args) . " given.", E_USER_ERROR);
					// TODO: check type, NULL, validation chain here
					$this->$key = $args[0];
				}
				return;
			}
		}
		trigger_error("Class " . get_class($this) . " has no method {$method}.", E_USER_ERROR);
	}
/*	http://www.garfieldtech.com/blog/magical-php-call
		if ($method) starts with get_ or set_, flag accordingly, get supposed field name (use camel case for get and set)
		camelCase($name) uncamelcase
		use __get and __set appropriately
	}
*/
	public static function getPostDateOffset($post_id) {
		// don't even think about using DateTime->diff() in PHP.  The DateInterval is impractical to work with.
		global $wpdb;
		$offset = $wpdb->get_var( $wpdb->prepare("SELECT TIMESTAMPDIFF(HOUR, post_date_gmt, post_date) as offset FROM $wpdb->posts WHERE ID = %d", $post_id) );
		//TODO: validate $offset or validate $post_id beforehand
		return $offset;
	}
	public static function getPostDateTime($post_id) {
		// TODO: it may be necessary to work from $datetime and not $datetime_gmt.  This would require
		// setting the timestamp in such a way that the datetime does not shift.
		$postdate = get_post_field('post_date', $post_id, 'db');
		if (is_wp_error($postdate))
			throw new \InvalidArgumentException("The post id {$post_id} is invalid.");
		$offset = Utility::getPostDateOffset($post_id);
		// assume the date is daylight savings time
		$name = timezone_name_from_abbr("", $offset * 3600, true);
		$timezone = new \DateTimeZone($name);
		$datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $postdate, $timezone);
		// now check to see if the datetime is in dst 
		$isdst = $timezone->getTransitions($datetime->getTimestamp(), $datetime->getTimestamp());
		var_dump($isdst);
		$isdst = $isdst[0]['isdst'];
		// if not, then the timezone is wrong
		if (!$isdst) {
			$name = timezone_name_from_abbr("", $offset * 3600, false);
			$datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $postdate, new \DateTimeZone($name));
		}
/*		// -----------------------------------
		$date_time_gmt = get_post_field('post_date_gmt', $post_id, 'db');
		if (is_wp_error($date_time_gmt))
			throw new InvalidArgumentException("The post id {$post_id} is invalid.");
		$datetime_gmt = DateTime::createFromFormat("Y-m-d H:i:s", $date_time_gmt, new DateTimeZone("UTC"));
		$datetime = clone $datetime_gmt;
		$offset = Utility::getPostDateOffset($post_id);

		$name = timezone_name_from_abbr("", $offset * 3600, true);
		$timezone = new DateTimeZone($name);
		$datetime->setTimezone($timezone);
		$isdst = $timezone->getTransitions($datetime->getTimestamp(), $datetime->getTimestamp())[0]['isdst'];
		if (!$isdst) {
			$name = timezone_name_from_abbr("", $offset * 3600, false);
			$timezone = new DateTimeZone($name);
			$datetime_gmt->setTimezone($timezone);
			$datetime = $datetime_gmt;
		}*/
		return $datetime;
	}
	// possible function to get post datetime with timezone offset for JavaScript functions
	//public static function getPostDateTimeJS($post_id) {}
	//SELECT @temp := TIMESTAMPDIFF(HOUR, post_date, post_date_gmt), CONCAT(post_date, IF(@temp >= 0,'+','-'), LPAD(ABS(@temp), 2, '0'), ':00') AS datetimez FROM wp_posts WHERE ID = 57;
	// public static function getPostDateModifiedTime($post_id);

			"SELECT count(ID) AS list FROM $wpdb->posts pt
			JOIN ($wpdb->postmeta ct, $wpdb->postmeta ft) ON (pt.ID = ct.post_id AND pt.ID = ft.post_id)
			WHERE pt.post_type = %s AND 
			ct.meta_key = 'course_type' AND ct.meta_value = 'TheTrainingMangerLMS\\LiveCourse' AND
			ft.meta_key = 'course_featured' AND ft.meta_value = 'true'",
			ttp_lms_prefix(Constants::CoursePostType)//, $type
