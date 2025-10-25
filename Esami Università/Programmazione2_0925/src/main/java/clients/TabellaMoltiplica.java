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

import java.util.ArrayList;
import java.util.List;
import java.util.Scanner;

import mole.Colonna;
import mole.Indice;
import mole.Tabella;
import utils.InputParsing;
import utils.OutputFormatter;

public class TabellaMoltiplica {
    public static void main(String[] args) {
       Scanner s = new Scanner(System.in);

        //tabella 1
        InputParsing.TableDescriptor td = (InputParsing.TableDescriptor) InputParsing.parseDescriptor(s.nextLine());
        List<Colonna<Object>> colonne = new ArrayList<>();
        for(int i=0; i<td.cols(); i++){
            InputParsing.ColumnDescriptor cd = (InputParsing.ColumnDescriptor) InputParsing.parseDescriptor(s.nextLine());
            String line=s.nextLine();
            InputParsing.IndexDescriptor id= (InputParsing.IndexDescriptor) InputParsing.parseDescriptor(line);
            Indice indice;
            if( id==null) indice = new Indice("", 0, cd.rows(),1);
            else{
                Object[] values = InputParsing.parseValues(s.nextLine(), id.len());
                String name = id.name();
                if (name == null) name = "";
                indice=new Indice(name,values);
                line=s.nextLine();
            }
            Object[] values = InputParsing.parseValues(line, cd.rows());
            String name = cd.name();
            if (name == null) name = "";
            Colonna<Object> colonna= new Colonna<>(name, indice,values);
            colonne.add(colonna);
            
        }

        Tabella<Object> tabella = new Tabella<>(colonne);
        tabella=tabella.mul(Integer.parseInt(args[0]));

        System.out.println(OutputFormatter.tableFormat(tabella)) ;

    }
}

