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
import utils.OutputFormatter;

public class ColonnaMoltiplica {
     public static void main(String[] args) {
        Indice indice=null;
        Scanner s = new Scanner(System.in);
        while(s.hasNext()){
        String line = s.nextLine();
        InputParsing.ColumnDescriptor cd = (InputParsing.ColumnDescriptor) InputParsing.parseDescriptor(line);
        if (cd == null) throw new IllegalArgumentException("Invalid column descriptor: " + line);
        line = s.nextLine();

        Object[] values = InputParsing.parseValues(line, cd.rows());
        String name = cd.name();
        if (name == null) {
            name = "";
        }
        Colonna<Object> colonna1;
        try {
            colonna1 = new Colonna<>(name,indice,InputParsing.parseValues(line, cd.rows()));
        } catch (Exception e) {
            colonna1 = new Colonna<>(name, InputParsing.parseValues(line, cd.rows()));
        }
        colonna1=colonna1.mul(Integer.parseInt(args[0]));
        System.out.println(OutputFormatter.columnFormat(colonna1));
    }
        }
}

