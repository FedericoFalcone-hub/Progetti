package main

import (
	"fmt"
)

// Vertice è un nodo del grafo che contiene la posizione nel piano e la slice delle cordinate che ha intorno
type Vertice struct {
	posizione Coordinate
	adiacenti []Coordinate
}

// il Piano contiene i vertici del grafo e le informazioni riguardanti gli ostacoli e gli automi
type Piano struct {
	ostacoli map[Coordinate]*Ostacolo
	vertici  map[Coordinate]*Vertice
	automa   map[Coordinate]*Automa
}

// Costruttori
func crea() *Piano {
	return &Piano{
		make(map[Coordinate]*Ostacolo), // Lista degli ostacoli (inizialmente vuota)
		make(map[Coordinate]*Vertice),  // Mappa dei vertici (inizialmente vuota)
		make(map[Coordinate]*Automa),   // Mappa degli automi (inizialmente vuota)
	}
}

func (p *Piano) stato(x, y int) string {
	coordinate := Coordinate{x, y}
	if p.automa[coordinate] != nil {
		return "A"
	}

	if p.ostacoli[coordinate] != nil {
		return "O"
	}
	return "E"
}

func (p *Piano) stampa() {
	fmt.Println("(")
	for _, automa := range p.automa {
		fmt.Println(automa.nome + " : " + automa.c.toString())
	}
	fmt.Println(")")
	fmt.Println("[")

	for _, ostacolo := range p.ostacoli {
		fmt.Println("(" + ostacolo.vertice1.toString() + ") (" + ostacolo.vertice2.toString() + ")")
	}

	fmt.Println("]")
}

// sistemare il controllo
func (p *Piano) aggiungiAutoma(x, y int, nome string) {
	coordinate := Coordinate{x, y}
	if p.ostacoli[coordinate]!=nil{
		return
	}
	
	automa := &Automa{coordinate, nome}
	p.automa[coordinate] = automa

	if _, esiste := p.vertici[coordinate]; !esiste &&  {
		adiacenti := []Coordinate{}

		if p.ostacoli[Coordinate{x + 1, y}] != nil || p.automa[Coordinate{x + 1, y}] != nil {
			adiacenti = append(adiacenti, Coordinate{x + 1, y})
			p.vertici[Coordinate{x + 1, y}].adiacenti = append(p.vertici[Coordinate{x + 1, y}].adiacenti, coordinate)
		}
		if p.ostacoli[Coordinate{x - 1, y}] != nil || p.automa[Coordinate{x - 1, y}] != nil {
			adiacenti = append(adiacenti, Coordinate{x - 1, y})
			p.vertici[Coordinate{x - 1, y}].adiacenti = append(p.vertici[Coordinate{x - 1, y}].adiacenti, coordinate)
		}
		if p.ostacoli[Coordinate{x, y + 1}] != nil || p.automa[Coordinate{x, y + 1}] != nil {
			adiacenti = append(adiacenti, Coordinate{x, y + 1})
			p.vertici[Coordinate{x, y + 1}].adiacenti = append(p.vertici[Coordinate{x, y + 1}].adiacenti, coordinate)
		}
		if p.ostacoli[Coordinate{x, y - 1}] != nil || p.automa[Coordinate{x, y - 1}] != nil {
			adiacenti = append(adiacenti, Coordinate{x, y - 1})
			p.vertici[Coordinate{x, y - 1}].adiacenti = append(p.vertici[Coordinate{x, y - 1}].adiacenti, coordinate)
		}
		p.vertici[coordinate] = &Vertice{coordinate, adiacenti}
	}

}

func main() {
	p := crea()
	p.aggiungiAutoma(1, 1, "1")
	p.aggiungiAutoma(5, 6, "2")
	fmt.Println(p.vertici[Coordinate{1, 1}].adiacenti)
	p.stampa()
}
