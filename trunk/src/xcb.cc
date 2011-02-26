/*
 * connection.c
 *
 *  Created on: Feb 22, 2011
 *      Author: root
 */
#include <stdio.h>
#include <stdlib.h>
#include <xcb/xcb.h>
#include <xcb/xcb_keysyms.h>
#include <xcb/xcb_atom.h>
#include <xcb/xcb_icccm.h>
#include <iostream>
#include <sstream>
#include <map>
#include <vector>
#include "phpwm.h"
#include "events.cc"

using namespace std;

xcb_connection_t *xconnection; // The connection to the X server.
int xnumber; // The active display number
xcb_screen_t *screen;
xcb_drawable_t root;

xcb_atom_t atom_desktop;

xcb_atom_t wm_delete_window;
xcb_atom_t wm_protocols;
const char* display_string;

class class_client {
	xcb_window_t window, parent_window, child_window;
	xcb_get_window_attributes_reply_t *attr;
	int window_class, currentWindowState, currentDragState;
	int lastButtonPress;
	int lastButtonRelease;

	void setSize(int width, int height) {
		phpwm_resize_window((int) window, width, height);
		//		xcb_change_window_attributes(xconnection, window, XCB_CONFIG_WINDOW_BORDER_WIDTH, (const uint32_t*)5);
	}
	int getId() {
		return (int) window;
	}

public:
	void setButtonPress(int intTime){
		lastButtonPress = intTime;
	}
	int getButtonPress(){
		return lastButtonPress;
	}
	void setButtonRelease(int intTime){
		lastButtonRelease = intTime;
	}
	int getButtonRelease(){
		return lastButtonRelease;
	}
	int getWindowState(){
		return currentWindowState;
	}
	void setWindowState(int state){
		currentWindowState = state;
	}
	int getDragState(){
		return currentDragState;
	}
	void setDragState(int state) {
		currentDragState = state;
	}
	class_client(xcb_window_t c) {
		window = c;
		attr = xcb_get_window_attributes_reply(xconnection, xcb_get_window_attributes(xconnection, c), NULL);
//		window_class = 0;
	}

	class_client() {

	}

	void takeOwnership() {
		registerWindowEvents();
//		php_args Args;
//		Args = php_args();
//		Args.add("action", "event_configure_request");
//		Args.add("id", getId());
//		runscript("core.php", Args);
	}
	void setParentWindow(int newParent) {
		parent_window = (xcb_window_t) newParent;
	}
	void setChildWindow(int newChild){
		child_window = (xcb_window_t) newChild;
	}
	int getChildWindow(){
		return (int) child_window;
	}
	int getParentWindow(){
		return (int) parent_window;
	}
	void setWindowClass(int windowclass) {
		window_class = windowclass;
		cout << "set window class " << window_class << endl;
	}
	int getWindowClass() {
		return window_class;
	}
	bool isChild() {
		if (parent_window) {
			return 1;
		}
		return 0;
	}
	void registerWindowEvents() {
		uint32_t mask = 0;
		uint32_t values[2];
		mask = XCB_CW_EVENT_MASK;
		values[0] = XCB_EVENT_MASK_EXPOSURE | XCB_EVENT_MASK_BUTTON_PRESS | XCB_EVENT_MASK_BUTTON_RELEASE | XCB_EVENT_MASK_POINTER_MOTION | XCB_EVENT_MASK_ENTER_WINDOW
				| XCB_EVENT_MASK_LEAVE_WINDOW | XCB_EVENT_MASK_KEY_PRESS | XCB_EVENT_MASK_KEY_RELEASE;
		xcb_change_window_attributes_checked(xconnection, window, mask, values);
		xcb_flush(xconnection);
	}
};
typedef map<int, class_client> WindowMap;
WindowMap windowlistmap;

void destroy() {
	xcb_disconnect(xconnection);
}
void startup_actions(){
	php_args Args;
	Args = php_args();
	Args.add("action", "application_startup");
	Args.add("root", screen->root);
	Args.add("display", display_string);

	runscript((char*)"core.php", Args);
}

int manage() {
	xcb_query_tree_reply_t *reply;
	int i;
	int len;
	xcb_window_t *children;
	class_client clientobj;
	uint32_t mask = 0;
	uint32_t values[2];
	xcb_void_cookie_t cookie;
	xcb_generic_error_t *error;

	/* Get some atoms. */
	atom_desktop = xcb_atom_get(xconnection, "_NET_WM_DESKTOP");
	wm_delete_window = xcb_atom_get(xconnection, "WM_DELETE_WINDOW");
	wm_protocols = xcb_atom_get(xconnection, "WM_PROTOCOLS");

	reply = xcb_query_tree_reply(xconnection, xcb_query_tree(xconnection, screen->root), 0);
	if (NULL == reply) {
		return -1;
	}
	len = xcb_query_tree_children_length(reply);
	children = xcb_query_tree_children(reply);
	//fetch all pre-existing windows
	for (i = 0; i < len; i++) {
		windowlistmap.insert(pair<int, class_client> (children[i], children[i]));
	}
//	WindowMap::iterator iter = windowlistmap.begin();
//	while (iter != windowlistmap.end()) {
//		iter->second.takeOwnership();
//		iter++;
//	}
	mask = XCB_CW_EVENT_MASK;

	values[0] = XCB_EVENT_MASK_SUBSTRUCTURE_REDIRECT | XCB_EVENT_MASK_STRUCTURE_NOTIFY | XCB_EVENT_MASK_SUBSTRUCTURE_NOTIFY;

	cookie = xcb_change_window_attributes_checked(xconnection, screen->root, mask, values);
	error = xcb_request_check(xconnection, cookie);
	//start looping events;
	startup_actions();
	watch_events();
	return 0;
}


void manageNewWindow(xcb_window_t newscreen) {
	windowlistmap.insert(pair<int, class_client> (newscreen, newscreen));
	windowlistmap.find(newscreen)->second.takeOwnership();
	cout << "Taking ownership of newscreen" << newscreen << endl;
}

int openDisplay(string requestedDisplay) {
	display_string = requestedDisplay.c_str();
	xconnection = xcb_connect(requestedDisplay.c_str(), &xnumber);
	if (xconnection == 0) {
		std::cout << "Unable to connect to xserver" << endl;
		return 1;
	} else {
		if (xcb_connection_has_error(xconnection)) {
			std::cout << "Unable to connect to display " << endl;
			return 1;
		} else {
			screen = xcb_setup_roots_iterator(xcb_get_setup(xconnection)).data;
			root = screen->root;
			std::cout << "phpwm active on " << xnumber << endl;
			return manage();
		}
	}
	return 0;
}

//void getXConfig() {
//	const xcb_setup_t *setup = xcb_get_setup(xconnection);
//	int i;
//	xcb_screen_iterator_t iter = xcb_setup_roots_iterator(setup);
//
//	// we want the screen at index screenNum of the iterator
//	for (i = 0; i < xnumber; ++i) {
//		xcb_screen_next(&iter);
//	}
//	screen = iter.data;
//}

int phpwm_config_width_in_pixels() {
	//	getXConfig();
	return screen->width_in_pixels;
}

vector<int> phpwm_window_list() {
	vector<int> arr;
	WindowMap::iterator iter = windowlistmap.begin();
	while (iter != windowlistmap.end()) {
		//cout << "window list:" << iter->first << endl;
//		if (!iter->second.isChild()) {
			arr.push_back(iter->first);
//		}
		iter++;
	}
	return arr;
}

int phpwm_config_height_in_pixels() {
	//	getXConfig();
	return screen->height_in_pixels;
}

int phpwm_config_white_pixel() {
	//	getXConfig();
	return screen->white_pixel;
}

int phpwm_config_black_pixel() {
	//	getXConfig();
	return screen->black_pixel;
}
int phpwm_window_generate_id() {
	xcb_window_t newID = xcb_generate_id(xconnection);
	return newID;
}
int phpwm_window_create_window(int windowId, int width, int height, int x, int y, int windowclass) {
	const uint32_t values[] = { screen->white_pixel, XCB_EVENT_MASK_EXPOSURE };
	xcb_create_window(xconnection, /* Connection          */
	XCB_COPY_FROM_PARENT, /* depth (same as root)*/
	windowId, /* window Id           */
	screen->root, /* parent window       */
	x, y, /* x, y                */
	width, height, /* width, height       */
	2, /* border_width        */
	XCB_WINDOW_CLASS_INPUT_OUTPUT, /* class               */
	screen->root_visual, /* visual              */
	XCB_CW_BACK_PIXEL | XCB_CW_EVENT_MASK, values); /* masks, not used yet */
	//	xcb_map_window (xconnection, windowId);
	//	xcb_flush (xconnection);
	windowlistmap.insert(pair<int, class_client> (windowId, windowId));

	windowlistmap.find(windowId)->second.setWindowClass(windowclass);
	return windowId;
}
int phpwm_window_configure(int windowId) {
	windowlistmap.insert(pair<int, class_client> (windowId, windowId));
	windowlistmap.find(windowId)->second.takeOwnership();
	return windowId;
}
int phpwm_window_get_class(int windowId) {
	cout << "get window class for " << windowId << ":" << windowlistmap.find(windowId)->second.getWindowClass() << endl;
	return windowlistmap.find(windowId)->second.getWindowClass();

}
int phpwm_window_map(int windowId) {
	xcb_map_window(xconnection, (xcb_window_t) windowId);
	xcb_flush(xconnection);
	return windowId;
}

int phpwm_window_unmap(int windowId) {
	xcb_unmap_window(xconnection, (xcb_window_t) windowId);
	xcb_flush(xconnection);
	return windowId;
}

int phpwm_move_window(int windowId, int x, int y) {
	cout << "phpwm_move_window " << windowId << " x:" << x << " y:" << y << endl;
	const uint32_t values[] = { x, y };
	xcb_configure_window(xconnection, (xcb_window_t) windowId, XCB_CONFIG_WINDOW_X | XCB_CONFIG_WINDOW_Y, values);
	xcb_flush(xconnection);
	return windowId;
}
int phpwm_window_border(int windowId, int width) {
	const uint32_t values[] = { width };
	xcb_configure_window(xconnection, (xcb_window_t) windowId, XCB_CONFIG_WINDOW_BORDER_WIDTH, values);
	xcb_flush(xconnection);
	return windowId;
}

int phpwm_resize_window(int windowId, int width, int height) {
	const uint32_t values[] = { width, height };
	xcb_configure_window(xconnection, (xcb_window_t) windowId, XCB_CONFIG_WINDOW_WIDTH | XCB_CONFIG_WINDOW_HEIGHT, values);
	xcb_flush(xconnection);
	return windowId;
}
int phpwm_raise_window(int windowId) {
	uint32_t values[] = { XCB_STACK_MODE_ABOVE };
	xcb_configure_window(xconnection, (xcb_window_t) windowId, XCB_CONFIG_WINDOW_STACK_MODE, values);
	xcb_flush(xconnection);
	return windowId;
}
int phpwm_lower_window(int windowId) {
	uint32_t values[] = { XCB_STACK_MODE_BELOW };
	xcb_configure_window(xconnection, (xcb_window_t) windowId, XCB_CONFIG_WINDOW_STACK_MODE, values);
	xcb_flush(xconnection);
	return windowId;
}

int phpwm_reparent_window(int windowId, int newWindowId) {
	const uint32_t values[] = { (xcb_window_t) windowId, XCB_STACK_MODE_BELOW };
	windowlistmap.find(windowId)->second.setParentWindow(newWindowId);
	windowlistmap.find(newWindowId)->second.setChildWindow(windowId);
	xcb_configure_window(xconnection, (xcb_window_t) windowId, XCB_CONFIG_WINDOW_SIBLING | XCB_CONFIG_WINDOW_STACK_MODE, values);
	xcb_flush(xconnection);
	return newWindowId;
}

int phpwm_get_child_window(int windowId){
	return windowlistmap.find(windowId)->second.getChildWindow();
}

int phpwm_get_parent_window(int windowId){
	return windowlistmap.find(windowId)->second.getParentWindow();
}

int phpwm_window_subscribe_events(int windowId){
	uint32_t mask = 0;
	uint32_t values[2];
	mask = XCB_CW_EVENT_MASK;
	values[0] = XCB_EVENT_MASK_EXPOSURE | XCB_EVENT_MASK_BUTTON_PRESS | XCB_EVENT_MASK_BUTTON_RELEASE | XCB_EVENT_MASK_POINTER_MOTION | XCB_EVENT_MASK_ENTER_WINDOW
			| XCB_EVENT_MASK_LEAVE_WINDOW | XCB_EVENT_MASK_KEY_PRESS | XCB_EVENT_MASK_KEY_RELEASE;
	xcb_change_window_attributes_checked(xconnection, (xcb_window_t) windowId, mask, values);
	xcb_flush(xconnection);
	return windowId;
}
int phpwm_report_button_press(int windowId, int timestamp){
	windowlistmap.find(windowId)->second.setButtonPress(timestamp);
	return windowId;
}
int phpwm_report_button_release(int windowId, int timestamp){
	windowlistmap.find(windowId)->second.setButtonRelease(timestamp);
	return windowId;
}
int phpwm_get_last_button_press(int windowId){
	return windowlistmap.find(windowId)->second.getButtonPress();
}
int phpwm_get_last_button_release(int windowId){
	return windowlistmap.find(windowId)->second.getButtonRelease();
}

int phpwm_get_window_state(int windowId){
	return windowlistmap.find(windowId)->second.getWindowState();
}

void phpwm_set_window_state(int windowId, int state){
	windowlistmap.find(windowId)->second.setWindowState(state);
}
int phpwm_get_drag_state(int windowId){
	return windowlistmap.find(windowId)->second.getDragState();
}
void phpwm_set_drag_state(int windowId, int state){
	windowlistmap.find(windowId)->second.setDragState(state);
}