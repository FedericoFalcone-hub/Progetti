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
import java.util.List;
import java.util.Scanner;

import mole.Indice;
import utils.InputParsing;
import utils.OutputFormatter;


public class IndiceFusione {
   public static void main(String[] args) {
        try (final Scanner s=new Scanner(System.in)){   
            while(s.hasNext()){
                // Indice 1
                String input=s.nextLine();
                String descrittore=input.substring(input.indexOf("[")+1,input.indexOf("]"));
                String[] parametri=descrittore.split(", ");
                int lunghezza=Integer.parseInt(parametri[0]);
                String nome="";
                if (parametri.length>1){
                    nome=parametri[1];
                }
                Object[] valori= InputParsing.parseValues(s.nextLine(),lunghezza);
                List<Object> lista = new java.util.ArrayList<>();

                lista.addAll(Arrays.asList(valori));
                Indice indice1=new Indice(nome,valori);

                
                // Indice 2
                input=s.nextLine();
                descrittore=input.substring(input.indexOf("[")+1,input.indexOf("]"));
                parametri=descrittore.split(",");
                lunghezza=Integer.parseInt(parametri[0]);
                nome="";
                if (parametri.length>1){
                    nome=parametri[1];
                }
                lista = new java.util.ArrayList<>();
                valori= InputParsing.parseValues(s.nextLine(),lunghezza);

                lista.addAll(Arrays.asList(valori));
                Indice indice2=new Indice(nome,valori);

                //fusione
                Indice indiceFuso=indice1.merge(indice2);
                
                //print
                System.out.println(OutputFormatter.indexFormat(indiceFuso));
            }
        }
    }
}
