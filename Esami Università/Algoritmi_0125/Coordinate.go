package main

import "strconv"

type Coordinate struct {
	x int
	y int
}

func (c Coordinate) toString() string {
	return (strconv.Itoa(c.x) + "," + strconv.Itoa(c.y))
}
