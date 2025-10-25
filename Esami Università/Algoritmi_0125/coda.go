package main

type listNode struct {
	item Vertice
	next *listNode
}

type linkedList struct {
	head, tail *listNode
}

func newNode(val Vertice) *listNode {
	return &listNode{val, nil}
}

func (l linkedList) isEmpty() bool {
	if l.head == nil {
		return true
	} else {
		return false
	}
}

func (l linkedList) enqueue(v Vertice) {
	n := newNode(v)
	if l.head == nil {
		l.head = n
		l.tail = n
	} else {
		l.tail.next = n
		l.tail = n
	}
}

func (l linkedList) dequeue() Vertice {
	x := l.head.item
	l.head = l.head.next
	if l.head == nil {
		l.tail = nil
	}
	return x
}
