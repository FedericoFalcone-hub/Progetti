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

public class TabellaImpilamento {
    public static void main(String[] args) {
        Scanner s = new Scanner(System.in);

        //tabella 1
        InputParsing.TableDescriptor td = (InputParsing.TableDescriptor) InputParsing.parseDescriptor(s.nextLine());
        List<Colonna<Object>> colonne = new ArrayList<>();
        for(int i=0; i<td.cols(); i++){
            InputParsing.ColumnDescriptor cd = (InputParsing.ColumnDescriptor) InputParsing.parseDescriptor(s.nextLine());
            InputParsing.IndexDescriptor id= (InputParsing.IndexDescriptor) InputParsing.parseDescriptor(s.nextLine());
            Object[] values = InputParsing.parseValues(s.nextLine(), id.len());
            String name = id.name();
            if (name == null) name = "";
            Indice indice=new Indice(name,values);
            values = InputParsing.parseValues(s.nextLine(), cd.rows());
            name=cd.name();
            if (name == null) name = "";
            Colonna<Object> colonna= new Colonna<>(name, indice,values);
            colonne.add(colonna);
        }

        Tabella<Object> tabella1 = new Tabella<>(colonne);

        //tabella 2
        td = (InputParsing.TableDescriptor) InputParsing.parseDescriptor(s.nextLine());
        colonne = new ArrayList<>();
        for(int i=0; i<td.cols(); i++){
            InputParsing.ColumnDescriptor cd = (InputParsing.ColumnDescriptor) InputParsing.parseDescriptor(s.nextLine());
            InputParsing.IndexDescriptor id= (InputParsing.IndexDescriptor) InputParsing.parseDescriptor(s.nextLine());
            Object[] values = InputParsing.parseValues(s.nextLine(), id.len());
            String name = id.name();
            if (name == null) name = "";
            Indice indice=new Indice(name,values);
            values = InputParsing.parseValues(s.nextLine(), cd.rows());
            name=cd.name();
            if (name == null) name = "";
            Colonna<Object> colonna= new Colonna<>(name, indice, values);
            colonne.add(colonna);
        }
        Tabella<Object> tabella2 = new Tabella<>(colonne);

        System.out.println(OutputFormatter.tableFormat(tabella1.stack(tabella2)));

    }
}
