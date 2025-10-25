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
import java.util.Iterator;
import java.util.Scanner;

import mole.Indice;
public class IndiceNumericoUltimi {
    public static void main(String[] args) {
        int inizio=Integer.parseInt(args[0]);
        int fine=Integer.parseInt(args[1]);
        int passo=Integer.parseInt(args[2]);
        Indice indice=new Indice("",inizio, fine, passo);

        Scanner s = new Scanner(System.in);
        while (s.hasNextLine()) {
            int input = Integer.parseInt(s.nextLine());
           
            //print
            Iterator<Object> it=indice.lastNIterator(input);
                String val="";
                while(it.hasNext()){
                    val+=it.next().toString() + ", ";
                }
                System.out.println(val.substring(0,val.length()-2));
        }

    }
}
