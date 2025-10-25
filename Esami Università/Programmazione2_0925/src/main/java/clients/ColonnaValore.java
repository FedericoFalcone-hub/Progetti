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

import mole.Colonna;
import mole.Indice;
import utils.InputParsing;

public class ColonnaValore {
    public static void main(String[] args) throws ClassNotFoundException, NoSuchMethodException, InstantiationException, IllegalAccessException {
        Indice indice=null;
        Scanner s = new Scanner(System.in);
        String line = s.nextLine();
        InputParsing.ColumnDescriptor cd = (InputParsing.ColumnDescriptor) InputParsing.parseDescriptor(line);
        if (cd == null) throw new IllegalArgumentException("Invalid column descriptor: " + line);
        line = s.nextLine();
        InputParsing.IndexDescriptor id = (InputParsing.IndexDescriptor) InputParsing.parseDescriptor(line);
        if (id != null) {
            Object[] labels = InputParsing.parseValues(s.nextLine(), cd.rows());
            String name = id.name();
            if (name == null) name = "";
            indice=new Indice(name,labels);
            line = s.nextLine();
        }
        Object[] values = InputParsing.parseValues(line, cd.rows());
        //crea colonna
        Object label = InputParsing.parseValues(args[0], 1)[0];
        String name = cd.name();
        if (name == null) name = "";
        Colonna<?> colonna;
        try {
             colonna=new Colonna<>(name, indice, InputParsing.parseValues(line, cd.rows()));
        } catch (Exception e) {
            colonna=new Colonna<>(name, InputParsing.parseValues(line, cd.rows()));
        }
        System.out.println(colonna.atLabel(label));

        
        
    }


}
