/*
 *  phpwm -- the PHP Based Window Manager
 *  Copyright (C) 2011  Davin C. Thompson <dthompso99@gmail.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
#include "phpwm.h"
#include <stdio.h>
#include <sapi/embed/php_embed.h>
#include <iostream>
#include <sstream>
#include <cstddef>
#include <map>
#include <xcb/xcb.h>

using namespace std;

class php_args {
	typedef map<string, string> ArgsList;
	ArgsList args;

public:
	php_args() {

	}
	void add(std::string key, std::string val) {
		args.insert(pair<string, string> (key, val));
	}
	void add(std::string key, int intval) {
		std::string val;
		std::stringstream out;
		out << intval;
		val = out.str();
		args.insert(pair<string, string> (key, val));
	}
	int getSize() {
		return args.size();
	}
	void fillZendArray(zval*& ARR) {
		ArgsList::iterator iter = args.begin();
		while (iter != args.end()) {
			addElement(ARR, (char*) iter->first.c_str(), (char*) iter->second.c_str());
			iter++;
		}
	}
	void addElement(zval*& ARR, char* key, char* val) {
		zval *new_element;
		MAKE_STD_ZVAL(new_element);
		ZVAL_STRING(new_element, val, 1);

		if (zend_hash_update(ARR->value.ht, key, strlen(key) + 1, (void *)&new_element, sizeof(zval *), NULL) == FAILURE) {
			cout << "FAILED TO ADD NEW ELEMENT TO ARRAY" << endl;
		}
	}
	char* toString() {
		ostringstream outs;
		ArgsList::iterator iter = args.begin();
		while (iter != args.end()) {
			outs << iter->first << "=" << iter->second << "&";
			cout << iter->first << " : " << iter->second << endl;
			iter++;
		}
		cout << outs.str() << endl;
		return (char*) outs.str().c_str();
	}
};

PHP_FUNCTION(phpwm_config_width_in_pixels) {
	RETURN_LONG(phpwm_config_width_in_pixels());
}

PHP_FUNCTION(phpwm_config_height_in_pixels) {
	RETURN_LONG(phpwm_config_height_in_pixels());
}

PHP_FUNCTION(phpwm_config_white_pixel) {
	RETURN_LONG(phpwm_config_white_pixel());
}

PHP_FUNCTION(phpwm_config_black_pixel) {
	RETURN_LONG(phpwm_config_black_pixel());
}

PHP_FUNCTION(phpwm_window_generate_id) {
	RETURN_LONG(phpwm_window_generate_id());
}

PHP_FUNCTION(phpwm_window_create_window) {
	int windownum, width, height, x, y, winlen, windowclass;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"llllll", &windownum, &width, &height, &x, &y, &windowclass, &winlen) == FAILURE) {
		cout << endl <<"Error Parsing new window parameters" << endl;
		return;
	}
	cout << endl <<"here" << endl;
	RETURN_LONG(phpwm_window_create_window(windownum, width, height, x, y, windowclass));
}
PHP_FUNCTION(phpwm_window_get_class) {
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_window_get_class(windownum));
}

PHP_FUNCTION(phpwm_move_window) {
	int windownum;
	int x;
	int y;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"lll", &windownum, &x, &y) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_move_window(windownum, x, y));
}

PHP_FUNCTION(phpwm_resize_window) {
	int windownum;
	int width;
	int height;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"lll", &windownum, &width, &height) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_resize_window(windownum, width, height));
}
PHP_FUNCTION(phpwm_window_border) {
	int windownum;
	int width;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"ll", &windownum, &width) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_window_border(windownum, width));
}

PHP_FUNCTION(phpwm_window_map) {
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_window_map(windownum));
}

PHP_FUNCTION(phpwm_window_unmap) {
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_window_unmap(windownum));
}
PHP_FUNCTION(phpwm_window_list) {
	std::vector<int> list = phpwm_window_list();
	std::vector<int>::iterator iter;
	array_init(return_value);
	for (iter = list.begin(); iter != list.end(); iter++) {
		add_next_index_long(return_value, *iter);
	}
}
PHP_FUNCTION(phpwm_raise_window) {
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_raise_window(windownum));
}
PHP_FUNCTION(phpwm_lower_window) {
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_lower_window(windownum));
}
PHP_FUNCTION(phpwm_reparent_window) {
	int windownum;
	int newwindownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"ll", &windownum, &newwindownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_reparent_window(windownum, newwindownum));
}

PHP_FUNCTION(phpwm_get_parent_window) {
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_get_parent_window(windownum));
}

PHP_FUNCTION(phpwm_get_child_window) {
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_get_child_window(windownum));
}

PHP_FUNCTION(phpwm_window_subscribe_events) {
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_window_subscribe_events(windownum));
}
PHP_FUNCTION(phpwm_report_button_press){
	int windownum, time;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"ll", &windownum, &time) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_report_button_press(windownum, time));
}

PHP_FUNCTION(phpwm_report_button_release){
	int windownum, time;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"ll", &windownum, &time) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_report_button_release(windownum, time));
}

PHP_FUNCTION(phpwm_get_last_button_press){
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum, &time) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_get_last_button_press(windownum));
}

PHP_FUNCTION(phpwm_get_last_button_release){
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum, &time) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_get_last_button_release(windownum));
}

PHP_FUNCTION(phpwm_set_drag_state){
	int windownum, state;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"ll", &windownum, &state) == FAILURE) {
		return;
	}
	phpwm_set_drag_state(windownum, state);
	RETURN_NULL();
}

PHP_FUNCTION(phpwm_get_drag_state){
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum, &time) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_get_drag_state(windownum));
}

PHP_FUNCTION(phpwm_set_window_state){
	int windownum, state;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"ll", &windownum, &state) == FAILURE) {
		return;
	}
	phpwm_set_window_state(windownum, state);
	RETURN_NULL();
}

PHP_FUNCTION(phpwm_get_window_state){
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum, &time) == FAILURE) {
		return;
	}
	RETURN_LONG(phpwm_get_window_state(windownum));
}

PHP_FUNCTION(phpwm_set_last_window_pos){
	int windownum, x, y, w, h;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"lllll", &windownum, &x, &y, &w, &h) == FAILURE) {
		return;
	}
	phpwm_set_last_window_pos(windownum, x, y, w, h);
	RETURN_NULL();
}
PHP_FUNCTION(phpwm_get_last_window_pos){
	int windownum;
	int* retval;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}
	retval = phpwm_get_last_window_pos(windownum);
	array_init(return_value);
	add_assoc_long(return_value, "x", retval[0]);
	add_assoc_long(return_value, "y", retval[1]);
	add_assoc_long(return_value, "w", retval[2]);
	add_assoc_long(return_value, "h", retval[3]);
}

PHP_FUNCTION(phpwm_get_geometry){
	xcb_get_geometry_reply_t* geom;
	int windownum;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, (char*)"l", &windownum) == FAILURE) {
		return;
	}

	geom = phpwm_get_geometry(windownum);
	array_init(return_value);
	add_assoc_long(return_value, "depth", geom->depth);
	add_assoc_long(return_value, "root", geom->root);
	add_assoc_long(return_value, "x", geom->x);
	add_assoc_long(return_value, "y", geom->y);
	add_assoc_long(return_value, "width", geom->width);
	add_assoc_long(return_value, "height", geom->height);
	add_assoc_long(return_value, "border_width", geom->border_width);


}

static void log_message(char *message) {
	cout << "PHP MESSAGE:" << message << endl;
	/* catch default output for log_errors; these are the messages
	 * that end up in your apache error log for example */
}
static void sapi_error(int type, const char *fmt, ...) {
	cout << "PHP sapi error: " << type << fmt << endl;
	/* Catch some low-level SAPI errors */
}
static function_entry app_functions[] = { PHP_FE(phpwm_window_subscribe_events, NULL) PHP_FE(phpwm_move_window, NULL)
PHP_FE(phpwm_config_black_pixel, NULL)
PHP_FE(phpwm_config_white_pixel, NULL)
PHP_FE(phpwm_config_height_in_pixels, NULL)
PHP_FE(phpwm_config_width_in_pixels, NULL)
PHP_FE(phpwm_window_generate_id, NULL)
PHP_FE(phpwm_window_create_window, NULL)
PHP_FE(phpwm_resize_window, NULL)
PHP_FE(phpwm_window_border, NULL)
PHP_FE(phpwm_window_map, NULL)
PHP_FE(phpwm_window_unmap, NULL)
PHP_FE(phpwm_window_list, NULL)
PHP_FE(phpwm_lower_window, NULL)
PHP_FE(phpwm_raise_window, NULL)
PHP_FE(phpwm_reparent_window, NULL)
PHP_FE(phpwm_window_get_class, NULL)
PHP_FE(phpwm_get_child_window, NULL)
PHP_FE(phpwm_get_parent_window, NULL)
PHP_FE(phpwm_report_button_release, NULL)
PHP_FE(phpwm_report_button_press, NULL)
PHP_FE(phpwm_get_last_button_release, NULL)
PHP_FE(phpwm_get_last_button_press, NULL)
PHP_FE(phpwm_set_drag_state, NULL)
PHP_FE(phpwm_get_drag_state, NULL)
PHP_FE(phpwm_set_window_state, NULL)
PHP_FE(phpwm_get_window_state, NULL)
PHP_FE(phpwm_set_last_window_pos, NULL)
PHP_FE(phpwm_get_last_window_pos, NULL)
PHP_FE(phpwm_get_geometry, NULL)
{	NULL, NULL, NULL} /* this last null line is important for some reason */
};

/*
 * Register module
 */

zend_module_entry app_module_entry = { STANDARD_MODULE_HEADER, (char*)"app",
app_functions,
NULL, /* MINIT */
NULL, /* MSHUTDOWN */
NULL, /* RINIT */
NULL, /* RSHUTDOWN */
NULL, /* MINFO */
(char*)"1.0",
STANDARD_MODULE_PROPERTIES};

int runscript(std::string name, php_args args){
	return runscript((char*)name.c_str(), args);
}

int runscript(char* name, php_args args) {
	//	string script = "include 'scripts/" + (std::string) name + "';";
	//	char *php_code = (char*) script.c_str();
	zend_file_handle script;
	script.type = ZEND_HANDLE_FP;
	script.filename = (char*) ("scripts/" + string(name)).c_str();
	script.opened_path = NULL;
	script.free_filename = 0;
	if (!(script.handle.fp = fopen(script.filename, "rb"))) {
		cout << "Unable to open script" << endl;
	}
	php_embed_module.log_message = log_message;
	php_embed_module.sapi_error = sapi_error;
	PHP_EMBED_START_BLOCK(NULL, NULL);
					zend_startup_module(&app_module_entry);
					zval *PHPWM;
					MAKE_STD_ZVAL(PHPWM);
					array_init(PHPWM);
					args.fillZendArray(PHPWM);
					ZEND_SET_SYMBOL(&EG(symbol_table), (char*)"_PHPWM", PHPWM);
					php_execute_script(&script TSRMLS_CC);
				PHP_EMBED_END_BLOCK();
	return 0;
}

