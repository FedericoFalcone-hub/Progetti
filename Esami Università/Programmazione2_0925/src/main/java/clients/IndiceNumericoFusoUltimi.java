/*

Copyright 2024 Massimo Santini

This file is part of "Programmazione 2 @ UniMI" teaching material.

This is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This material is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this file.  If not, see <https://www.gnu.org/licenses/>.

*/

package clients;
import java.util.Arrays;
import java.util.Iterator;
import java.util.List;
import java.util.Scanner;

import mole.Indice;
import mole.IndiceFuso;
import utils.InputParsing;

public class IndiceNumericoFusoUltimi {
    
    public static void main(String[] args) {
        int inizio = Integer.parseInt(args[0]);
        int fine = Integer.parseInt(args[1]);
        int passo = Integer.parseInt(args[2]);
        
        Scanner s = new Scanner(System.in);
        Indice indice1= new Indice("",inizio, fine, passo);
        while (s.hasNextLine()) {
            String input=s.nextLine();
            if (!input.contains("[") || !input.contains("]")) {
            continue;
    }
                String descrittore=input.substring(input.indexOf("[")+1,input.indexOf("]"));
                String[] parametri=descrittore.split(",");
                int lunghezza=Integer.parseInt(parametri[0]);
                String nome="";
                if (parametri.length>1){
                    nome=parametri[1];
                }
                String etichette=s.nextLine();
                List<Object> lista = new java.util.ArrayList<>(); 
                Object[] valori = InputParsing.parseValues(etichette, lunghezza);
                
                lista.addAll(Arrays.asList(valori));
               Indice indice=new Indice(nome,valori);
                
                IndiceFuso indiceFuso=(IndiceFuso)indice1.merge(indice);
               //print
                Iterator<Object> it=indiceFuso.lastNIterator(10);
                String val="";
                while(it.hasNext()){
                    val+=it.next().toString() + ", ";
                }
                System.out.println(val.substring(0,val.length()-2));
        }
    }
}
