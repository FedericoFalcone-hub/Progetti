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

public class IndiceMedesimo {
    public static void main(String[] args) {
        try (final Scanner s=new Scanner(System.in)){ 
            while(s.hasNext()){
                utils.InputParsing.IndexDescriptor id = (utils.InputParsing.IndexDescriptor) utils.InputParsing.parseDescriptor(s.nextLine());
                Object[] labels = utils.InputParsing.parseValues(s.nextLine(), id.len());
                String name = id.name();
                if (id.name() == null) {
                    name = "";
                }
                
                Indice indice1=new Indice(name,labels);

                id= (utils.InputParsing.IndexDescriptor) utils.InputParsing.parseDescriptor(s.nextLine());
                labels = utils.InputParsing.parseValues(s.nextLine(), id.len());
                name = id.name();
                if (id.name() == null) {
                    name = "";
                }
                Indice indice2=new Indice(name,labels);

                System.out.println(indice1.same(indice2));
                
            }  
            }
    }
}
