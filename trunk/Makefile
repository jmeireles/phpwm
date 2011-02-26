LIBS=-lphp5 $(shell php-config --libs) $(shell pkg-config --cflags --libs xcb-atom ) -lxcb-keysyms -lxcb-icccm
INCLUDES=$(shell php-config --includes)
LIBDIRS=-L$(shell php-config --prefix)/lib
PHP_EXE=$(shell php-config --prefix)/bin/php
CC=g++
CFLAGS=-g -Wall

phpwm: phpwm.o
	$(CC) $(CFLAGS) $(LIBDIRS) -o phpwm $(LIBS) phpwm.o

phpwm.o: src/phpwm.cc
	$(CC) $(CFLAGS) $(INCLUDES) -c src/phpwm.cc

clean:
	rm -f *.o phpwm
	
all: phpwm
	
