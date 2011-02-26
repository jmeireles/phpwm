/*
 * phpEmbed.c
 *
 *  Created on: Feb 22, 2011
 *      Author: root
 */
#include "phpwm.h"
#include <stdio.h>
#include <sapi/embed/php_embed.h>
#include <iostream>
#include <sstream>
#include <cstddef>
#include <map>

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
	phpwm_get_drag_state(windownum);
	RETURN_NULL();
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

int execute_test(int argc, char **argv) {
	char *php_code = (char*) "include 'scripts/core.php';";
	php_embed_module.log_message = log_message;
	php_embed_module.sapi_error = sapi_error;
	PHP_EMBED_START_BLOCK(argc, argv);
					zend_startup_module(&app_module_entry);
					zend_eval_string(php_code, NULL, (char*) "Embedded code" TSRMLS_CC);
				PHP_EMBED_END_BLOCK();
	return 0;
}
