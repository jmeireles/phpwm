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

#include <stdio.h>
#include <stdlib.h>
#include <iostream>
#include "script.cc"
#include "xcb.cc"
#include "phpwm.h"

using namespace std;


int main(int argc, char **argv) {
	string requestedDisplay;
//	   cout << "argc = " << argc << endl;
	   for(int i = 1; i < argc; i++){
		   if ((string)argv[i] == "-d"){
			   requestedDisplay=argv[i+1];
		   }
//	      cout << "argv[" << i << "] = " << argv[i] << endl;
	   }
	   cout << "requestedDisplay = " << requestedDisplay << endl;

	if (openDisplay(requestedDisplay)==0) {
		std::cout << "unable to connect to requested display" << endl;
//		execute_test(argc, argv);
		//start our main loop
	} else {
		//TODO: any cleanup code here.
		std::cout << "phpwm shutting down, unable to open display " << endl;
	}
	destroy();
	return 0;
}
