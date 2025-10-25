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

import java.util.Scanner;

import mole.Indice;
import utils.InputParsing;

public class IndicePosizione {
    public static void main(String[] args) {
        try (final Scanner s=new Scanner(System.in)){   
            Object label = InputParsing.parseValues(args[0], 1)[0];
            InputParsing.IndexDescriptor id= (InputParsing.IndexDescriptor) InputParsing.parseDescriptor(s.nextLine());
                
                Object[] labels= InputParsing.parseValues(s.nextLine(),id.len());
                String nome = id.name();
                if(id.name()==null){
                    nome = "";
                }


                Indice indice=new Indice(nome,labels);
                try {
                    System.out.println(indice.positionOf(label));
                } catch (Exception e) {
                    System.out.println(-1);
                }
                
            }
    }
}
