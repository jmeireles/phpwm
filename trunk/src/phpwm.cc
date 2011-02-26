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
