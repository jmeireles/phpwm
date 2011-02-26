killall phpwm
Xephyr :1 &
make clean && make

sleep 1
./phpwm -d 127.0.0.1:1 &
sleep 1
xterm -display 127.0.0.1:1 &

