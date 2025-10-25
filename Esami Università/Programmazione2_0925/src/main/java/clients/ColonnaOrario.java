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

import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.Scanner;

import mole.Colonna;
import mole.Indice;
import utils.InputParsing;
import utils.OutputFormatter;

public class ColonnaOrario {
    public static void main(String[] args) {
        Indice indice=null;
        Scanner s = new Scanner(System.in);
        while(s.hasNext()){
        String line = s.nextLine();
        InputParsing.ColumnDescriptor cd = (InputParsing.ColumnDescriptor) InputParsing.parseDescriptor(line);
        if (cd == null) throw new IllegalArgumentException("Invalid column descriptor: " + line);
        line = s.nextLine();
        String name;
        InputParsing.IndexDescriptor id = (InputParsing.IndexDescriptor) InputParsing.parseDescriptor(line);
        if (id != null) {
            name = id.name();
            if (name == null) {
                name = "";
            }
            Object[] labels = InputParsing.parseValues(s.nextLine(), cd.rows());
            
           indice =new Indice(name,labels);
            line = s.nextLine();
        }
        name= cd.name();
        if (cd.name() == null) {
            name="";
        }
        Object[] values = InputParsing.parseValues(line, cd.rows());
        
        ArrayList<LocalDateTime> localDateTimeValues = new ArrayList<>();
        for (Object v : values) {
            if (v instanceof LocalDateTime) {
                localDateTimeValues.add((LocalDateTime) v);
            } else if (v != null) {
                localDateTimeValues.add(LocalDateTime.parse(v.toString()));
            } else {
                localDateTimeValues.add(null);
            }
        }

        Colonna<?> colonna1;
        try {
            colonna1 = new Colonna<>(name, indice, InputParsing.parseValues(line, cd.rows()));
        } catch (Exception e) {
            colonna1 = new Colonna<>(name, InputParsing.parseValues(line, cd.rows()));
        }

        colonna1 = colonna1.changeTimeFormat();

        System.out.println(OutputFormatter.columnFormat(colonna1));
        }
}
}
