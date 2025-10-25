package mole;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;
import java.util.NoSuchElementException;

/**
 * Un indice fuso. <br>
 * 
 * Un indice fuso è l'indice risultante della fusione di due indici.
 * 
 * Gli oggetti di {@code IndiceFuso} sono immutabili
 * 
 * IndiceFuso è un sottotipo di Indice che reimplementa le operazioni di accesso alle etichette.
 */
public class IndiceFuso extends Indice {

    /**L'indice contenente le etichette non presenti nell'indice base */
    private final Indice indiceFuso;

    /*-
     * AF: - l'indice con cui è stata eseguita la fusione è contenuto nel campo indiceFuso, per il resto vale l'AF del supertipo.
     *        
     * RI: - indiceFuso != null;
     *     - indiceFuso non contiene etichette già presenti in super;
     *     - gli attributi della superclasse sono privati, quindi non interessano questa RI.
     *     
     */

    /**
     * Inizializza this in modo che inizializzi super con indice1 e abbia indiceFuso come un indice che contiene le etichette di indice2 non presenti in indice1.
     * 
     * EFFECTS: se indice1 o indice2 sono nulli, lancia {@code IllegalArgumentException},
     *          se la lunghezza dell'indice risultate è troppo grande, lancia {@code IllegalArgumentException},
     *          altrimenti inizializza super con indice1 e indiceFuso con le etichette di indice2 non presenti in indice1.
     * 
     * @param indice1 l'indice originale.
     * @param indice2 l'indice da fondere in indice1.
     * @throws IllegalArgumentException se uno dei due indici è nullo.
     */
    protected IndiceFuso(Indice indice1, Indice indice2) throws IllegalArgumentException {
        super(indice1);
        if(indice1 == null) throw new IllegalArgumentException("IndiceFuso.<init>: indice1 non può essere null.");
        if(indice2 == null) throw new IllegalArgumentException("IndiceFuso.<init>: indice2 non può essere null.");
        List<Object> indice2Labels = new java.util.ArrayList<>();
        for (Object etichetta : indice2) {
            try {
                indice1.positionOf(etichetta);
            } catch (Exception e) {
                indice2Labels.add(etichetta);
            }
        }
        if (indice1.length() + indice2Labels.size() < 0) throw new IllegalArgumentException("IndiceFuso.<init>: L'indice è troppo grande.");
        this.indiceFuso= new Indice(indice2.getName(),indice2Labels);
        
    }

    @Override
    public int positionOf(Object etichetta) throws NoSuchElementException {
        try {
            return super.positionOf(etichetta);
        } catch (Exception e) {
            try {
                return indiceFuso.positionOf(etichetta) + super.length();
            } catch (Exception ex) {
                throw new NoSuchElementException("IndiceFuso.positionOf: L'etichetta " + etichetta + " non è presente nell'indice.");
            }
        }
    }

    @Override
    public Object labelAt(int pos){

        if (pos <= super.length()) {
            return super.labelAt(pos);
        } else {
            return indiceFuso.labelAt(pos - super.length());
        }
    }

    @Override
    public int length() {
        return super.length() + indiceFuso.length();
    }

    @Override
    public int hashCode() {
        int result = super.hashCode();
        result = 31 * result + indiceFuso.hashCode();
        result = 31 * result + length();
        return result;
    }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof IndiceFuso)) return false;
        IndiceFuso other = (IndiceFuso) obj;
        return length() == other.length() &&
               super.equals(other) &&
               indiceFuso.equals(other.indiceFuso);
    }

    @Override
    public Iterator<Object> iterator()throws NoSuchElementException {
        return new Iterator<Object>(){
            private int i = 1;
            private final int lunghezza = length();

            @Override
            public boolean hasNext() {
                return i <= lunghezza;
            }

            @Override
            public Object next() {
                if (!hasNext()) {
                    throw new NoSuchElementException("Indice.iterator.next: Non ci sono più elementi da iterare.");
                }
                Object label = labelAt(i);
                i++;
                return label;
            }
        };
    }

    @Override
    public Iterator<Object> lastNIterator(int n){
        List<Object> lista=new ArrayList<>();
        int lunghezza=super.length()+indiceFuso.length();
        for(long i=lunghezza-n+1; i<=lunghezza; i++){
            lista.add(labelAt((int)i));
        }

        return lista.iterator();
    }

     @Override
    public Iterator<Object> jumpIterator(int jump){
        List<Object> lista=new ArrayList<>();
        lista.add(labelAt(1));
        for(int i=jump; i<=super.length() + indiceFuso.length();){
            lista.add(labelAt(i+1));
            i+=jump;
            if(i<0) break;
        }
        return lista.iterator();
    }

    @Override
    public String toString(){
        String output= "Nome " + getName() + " [";
        for(int i=1; i<=length(); i++){
            output+= labelAt(i);
            if(i<length()) output+=", ";
        }
        output+="]";
        return output;
    }

}
