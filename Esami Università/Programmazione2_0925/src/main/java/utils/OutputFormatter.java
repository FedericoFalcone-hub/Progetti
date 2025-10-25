package utils;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

import mole.Colonna;
import mole.Indice;
import mole.Tabella;
/**
 * Classe di utility per formattare l'output di indici, colonne e tabelle.
 * 
 */
public class OutputFormatter {
    
    /**
     * Costruttore privato per evitare l'istanza della classe.
     */
    private OutputFormatter() {

    }
    /**
     * Restituisce una stringa che rappresenta la tabella formattata.
     * 
     * EFFECTS: se la tabella è null, lancia {@code NullPointerException},
     *          altrimenti restituisce una stringa che rappresenta la tabella formattata.
     * 
     * @param tabella la tabella da formattare.
     * @return una stringa che rappresenta la tabella formattata.
     * @throws NullPointerException se la tabella è null.
     */
    public static String tableFormat(Tabella<?> tabella){
        if (tabella == null) throw new NullPointerException("La tabella non può essere null");
        String risultato="";
        Indice indice= tabella.getIndice();
        Iterator<Object> indexIt=indice.iterator();
        String riga="";

        //Indice
        String name=indice.getName();
        List<String> valoriIndice=new ArrayList<>();
        int lun;
        if(name.equals("")) lun=2;
        else lun=name.length()+1;
        String intestazione=name+" ";

        while(indexIt.hasNext()){
            String val=indexIt.next().toString();
            valoriIndice.add(val + " |");
            if (lun<val.length()+1) lun=val.length()+1;
        }
        for (int i=0; i<valoriIndice.size(); i++){
            String val=valoriIndice.get(i);
            if(val.length()<= lun){
                for(int j=val.length(); j<=lun; j++) valoriIndice.set(i, " " + val);
            } 
        }
        if(name.length()<lun){
            for(int i=name.length(); i<lun-1; i++) intestazione=" " + intestazione;
        }
        for (int i=0; i<lun; i++){
            riga+="-";
        }
        riga+="+";
        
        if (intestazione.length()<lun){
            for (int i=intestazione.length(); i<lun;i++){
                intestazione+=" ";
            }
        }

        for(int i=0; i<valoriIndice.size();i++){
            String val=valoriIndice.get(i);
            if(val.length()<lun){
              for(int j=val.length(); j<=lun; j++) val=" " + val;
              valoriIndice.set(i, val);                  
            } 
        }

        //Colonne
        List<String[]> valoriColonne=new ArrayList<>();
        
        for(Colonna<?> colonna: tabella){
            String[] valori=new String[colonna.length()];
            lun=colonna.getName().length();
            intestazione+="| " +colonna.getName() + " ";
            Iterator <?> columnIt=colonna.iterator();
            int i=0;
            while(columnIt.hasNext()){
                String val;
                try {
                     val=columnIt.next().toString();
                } catch (Exception e) {
                    val="";
                }
               
                valori[i]=" " + val;
                if(lun<val.length()) lun=val.length();
                i++;
            }
            valoriColonne.add(valori);

            for(int j=0; j<valori.length; j++){
                String val=valori[j];
                if(val.length()<=lun){
                    
                    for(int k=val.length(); k<=lun;k++) valori[j]=valori[j]+ " ";
                }
            }
            if (colonna.getName().length()<lun){

                for(int j=colonna.getName().length(); j<lun;j++) intestazione+=" "; 
            }
            
            for (int j=0; j<lun+2; j++){
                riga+="-";
            }
            riga+="+";
        }

        riga=riga.substring(0, riga.length()-2);
        risultato+=intestazione+" \n" + riga + "\n";
    

        for(int i=0; i<tabella.getRowCount(); i++){
            risultato+=valoriIndice.get(i);
            for(int j=0; j<tabella.getColumnCount(); j++){
                String val=valoriColonne.get(j)[i];
                risultato+=val;
                
                lun=tabella.columnAtPosition(j+1).getName().length();
                if(val.length()<lun){
                    for(int k=0; k<=lun-val.length();k++) risultato+=" ";
                }
                if(j!=tabella.getColumnCount()-1) risultato+=" |";
            }
            risultato+="\n";
        }
        return risultato;
    }

    /**
     * Restituisce una stringa che rappresenta la colonna formattata.
     * 
     * EFFECTS: se la colonna è null, lancia {@code NullPointerException},
     *          altrimenti restituisce una stringa che rappresenta la colonna formattata.
     * 
     * @param colonna la colonna da formattare.
     * @return una stringa che rappresenta la colonna formattata.
     * @throws NullPointerException se la colonna è null.
     */
    public static String columnFormat(Colonna<?> colonna){
        if (colonna == null) throw new NullPointerException("La colonna non può essere null");
        String risultato= "";
        Indice indice=colonna.getIndice();
        Iterator<Object> indexIt=indice.iterator();

        String riga="";

        //Indice
        String name=indice.getName();
        List<String> valoriIndice=new ArrayList<>();
        int lun;
        if(name.equals("")) lun=2;
        else lun=name.length()+1;
        String intestazione=name+" ";

        while(indexIt.hasNext()){
            String val=indexIt.next().toString();
            valoriIndice.add(val + " |");
            if (lun<val.length()+1) lun=val.length()+1;
        }
        for (int i=0; i<valoriIndice.size(); i++){
            String val=valoriIndice.get(i);
            if(val.length()<= lun){
                for(int j=val.length(); j<=lun; j++) valoriIndice.set(i, " " + val);
            } 
        }

        if(name.length()<lun){
            for(int i=name.length(); i<lun-1; i++) intestazione=" " + intestazione;
        }
        for (int i=0; i<lun; i++){
            riga+="-";
        }
        riga+="+";
        
        if (intestazione.length()<lun){
            for (int i=intestazione.length(); i<lun;i++){
                intestazione=" "+intestazione;
            }
        }

        intestazione+="| " +colonna.getName() + " ";
        for(int i=0; i<valoriIndice.size();i++){
            String val=valoriIndice.get(i);
            if(val.length()<lun){
              for(int j=val.length(); j<=lun; j++) val=" " + val;
              valoriIndice.set(i, val);                  
            } 
        }

        //Colonna
        Iterator<?> columnIt=colonna.iterator();
        int i=0;
        lun=colonna.getName().length();

        if (colonna.getName().length()+2<lun){
                for(int j=0; j<lun-colonna.getName().length();j++) intestazione+=" "; 
            }
           
        String valori="";
       while(columnIt.hasNext()){
            String val="";
            try {
                val=columnIt.next().toString();
            } catch (Exception e) {
                val="";
            }
            if(val.length()> lun) lun=val.length();
            valori+=valoriIndice.get(i) + " " + val +"\n";
            i++;

        }  for (int j=0; j<lun+2; j++){
                riga+="-";
            }
            riga+="+";
        riga=riga.substring(0, riga.length()-2);
        risultato+=intestazione+" \n" + riga + "\n";
        risultato+=valori;
        return risultato;
    }

    /**
     * Restituisce una stringa che rappresenta l'indice formattato.
     * 
     * EFFECTS: se l'indice è null, lancia {@code NullPointerException},
     *          altrimenti restituisce una stringa che rappresenta l'indice formattato.
     * 
     * @param indice l'indice da formattare.
     * @return una stringa che rappresenta l'indice formattato.
     * @throws NullPointerException se l'indice è null.
     */
    public static String indexFormat(Indice indice){
        if (indice == null) throw new NullPointerException("L'indice non può essere null");
        Iterator<Object> it=indice.iterator();
        String name=indice.getName();
        int lun=name.length();
        String risultato="";
        List<String> valori=new ArrayList<>();
        while(it.hasNext()){
            String val=it.next().toString();
            
            valori.add(val);
                if (lun<val.length()) lun=val.length();
            }
        if(name.length()<lun){
            for(int i=name.length(); i<lun; i++) name=" " + name;
        }
        risultato+=name + "\n";
        for (int i=0; i<lun; i++){
            risultato+="-";
        }

        for(int i=0; i<valori.size(); i++){
            String val=valori.get(i);
            if(val.length()<lun){
                for(int j=val.length(); j<lun; j++) val=" " + val;
                valori.set(i, val);                  
            } 
        }
        for (String val: valori){
            risultato+="\n"+val;
        }
        return risultato;
    }
}
