package mole;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.NoSuchElementException;
import java.util.function.Function;
import java.util.HashSet;
import java.util.Set;

/**
 * Una tabella. <br>
 * 
 * Una tabella è un insieme di colonne con indice medesimo.
 * 
 * Le colonne sono dello stesso tipo.
 * 
 * Gli oggetti di {@code Tabella} sono immutabili.
 * 
 * @param <V> il tipo di valore delle colonne della tabella.
 * 
 * Fornisce metodi che permettono di ottenere informazioni, iterare sulle colonne, impilare e affiancare tabelle.   
 */
public class Tabella<V> implements Iterable<Colonna<V>> {

    /**La lista contente le colonne della tabella.*/
    private final List<Colonna<V>> colonne;

    /*-
     * AF: - le colonne sono contenute nella relativa lista colonne.
     * 
     * RI: - colonne != null;
     *     - colonne.size() > 0;
     *     - ∀e ∈ colonne: e != null;
     *     - ∀e1, e2 ∈ colonne: e1 != e2;
     *     - ∀e1, e2 ∈ colonne: e1.getIndice() == e2.getIndice();
     *     - ∀e ∈ colonne: e.getName() != "";
     */

    /**
     * Inizializza una tabella in modo che abbia le colonne specificate.
     * 
     * EFFECTS: se colonne è null, lancia {@code NullPointerException},
     *          se colonne contiene valori null, lancia {@code NullPointerException},
     *          se colonne contiene colonne con indici diversi oppure contiene colonne duplicate, lancia {@code IllegalArgumentException},
     *          se colonne contiene colonne con nomi vuoti, le rinomina in "Column_i" dove i è l'indice della colonna e restituisce una tabella con le colonne specificate.
     * 
     * @param colonne le colonne della tabella.
     * @throws NullPointerException se colonne è null.
     * @throws IllegalArgumentException se i valori di colonne hanno indici diversi oppure contiene colonne duplicate.
     * 
     */
    public Tabella(List<Colonna<V>> colonne) throws NullPointerException, IllegalArgumentException {
        if (colonne == null) throw new NullPointerException("Tabella.<init>: Colonne non può essere null");
        List<Colonna<V>> temp=new ArrayList<>();
        Set<Colonna<V>> set=new HashSet<>();
        for(int i=0;i<colonne.size(); i++){
            Colonna<V> col=colonne.get(i);
            if(i!=0 && !col.getIndice().same(colonne.get(0).getIndice())) throw new IllegalArgumentException("Tabella.<init>: L'indice della colonna deve essere medesimo per tutte le tabelle");
            if(!set.add(col)) throw new IllegalArgumentException("Tabella.<init>: Le colonne non possono essere duplicate: " + col.getName()); 
            if (col.getName().equals("")) temp.add(col.withName("Column_"+i));
            else temp.add(col);
        }
        this.colonne = Collections.unmodifiableList(new ArrayList<>(temp));

    }

    /**
     * Restituisce l'indice di this.
     * 
     * EFFECTS: restituisce l'indice di this.
     * 
     * @return l'indice di this.
     */
    public Indice getIndice(){
        return colonne.get(0).getIndice();
    }

    /**
     * Restituisce il numero di righe della tabella.
     * 
     * EFFECTS: restituisce il numero di righe della tabella.
     * 
     * @return il numero di righe della tabella.
     */
    public int getRowCount() {
        return colonne.get(0).length();
    }

    /**
     * Restituisce il numero di colonne in this.
     * 
     * EFFECTS: restituisce il numero di colonne in this.
     * 
     * @return il numero di colonne in this.
     */
    public int getColumnCount(){
        return colonne.size();
    }

    /**
     * Restituisce la colonna alla posizione specificata. 
     * 
     * EFFECTS: se colPosition è minore di 1 o maggiore del numero di colonne, lancia {@code IllegalArgumentException},
     *          altrimenti restituisce la colonna alla posizione specificata.
     * 
     * @param colPosition la posizione della colonna da restituire (1-based).
     * @return la colonna alla posizione specificata.
     * @throws IllegalArgumentException se colPosition è minore di 1 o maggiore del numero di colonne.
     */
    public Colonna<V> columnAtPosition(int colPosition) throws IllegalArgumentException {
        if (colPosition<1) throw new IllegalArgumentException("Tabella.columnAtPosition: Posizione non valida");
        if (colPosition > colonne.size()) throw new IllegalArgumentException("Tabella.columnAtPosition: Posizione non valida: " + colPosition + " > " + colonne.size());
        return colonne.get(colPosition-1);
    }
    
    /**
     * Resistuisce la colonna con il nome specificato.
     * 
     * EFFECTS: se label è null, lancia {@code NullPointerException},
     *          se non esiste una colonna con il nome specificato, lancia {@code NoSuchElementException},
     *          altrimenti restituisce la colonna con il nome specificato.
     * 
     * @param label il nome della colonna da cercare.
     * @return la colonna con il nome specificato.
     * @throws NullPointerException se label è nullo.
     * @throws NoSuchElementException se non esiste una colonna con il nome specificato.
     */
     public Colonna<V> columnAtLabel(Object label) throws NullPointerException, NoSuchElementException {
        if(label == null) throw new NullPointerException("Tabella.columnAtLabel: Label non può essere null");
        for (Colonna<V> colonna : colonne) {
            if(colonna.getName().equals(label.toString())) {
                return colonna;
            }
        }
        throw new NoSuchElementException("Tabella.columnAtLabel: Colonna con nome " + label + " non trovata");
    }

    /**
     * Restituisce un valore di this dati la posizione dell'indice e della colonna.
     * 
     * EFFECTS: se rowPosition o colPosition sono minori di 1, lancia {@code IllegalArgumentException},
     *          se rowPosition è maggiore della lunghezza dell'indice, lancia {@code IndexOutOfBoundsException},
     *          se colPosition è maggiore del numero di colonne, lancia {@code IndexOutOfBoundsException},
     *          altrimenti restituisce un valore di this dati la posizione dell'indice e della colonna.
     * 
     * @param rowPosition la posizione dell'indice (1-based).
     * @param colPosition la posizione della colonna (1-based).
     * @return un valore di this dati la posizione dell'indice e della colonna.
     * @throws IllegalArgumentException se rowPosition o colPosition sono null.
     * @throws IndexOutOfBoundsException se rowPosition è maggiore della lunghezza dell'indice o colPosition è maggiore del numero di colonne.
     * 
     */
    public V atPosition(int rowPosition,int colPosition) throws IllegalArgumentException, IndexOutOfBoundsException {
        if(rowPosition <1) throw new IllegalArgumentException("Tabella.atPosition: Row position non valida: " + rowPosition);
        if(colPosition <1) throw new IllegalArgumentException("Tabella.atPosition: Col position non valida: " + colPosition);

        Colonna<V> colonna;
        try {
            colonna = columnAtPosition(colPosition);
        } catch (Exception e) {
            throw new IndexOutOfBoundsException("Tabella.atPosition: colPosition fuori dai limiti: " + colPosition);
        }

        try {
            return colonna.atPosition(rowPosition);
        } catch (Exception e) {
            throw new IndexOutOfBoundsException("Tabella.atPosition: rowPosition fuori dai limiti: " + rowPosition);
        }
        
    }

    /**
     * Restituisce un valore di this dati etichetta dell'indice e nome di colonna.
     * 
     * EFFECTS: se rowLabel o colLabel sono null, lancia {@code NullPointerException},
     *           se non esiste una colonna con il nome colLabel, lancia {@code NoSuchElementException},
     *           se non esiste un indice con etichetta rowLabel, lancia {@code NoSuchElementException},
     *           altrimenti restituisce un valore di this dati l'etichetta di colonna e di indice.
     * 
     * @param rowLabel l'etichetta dell'indice.
     * @param colLabel il nome della colonna.
     * @return un valore di this dati l'etichetta di colonna e di indice.
     * @throws NullPointerException se rowLabel o colLabel sono null.
     * @throws NoSuchElementException se non esiste una colonna con il nome colLabel o un indice con etichetta rowLabel.
     */
    public V atLabel(Object rowLabel, Object colLabel) throws NullPointerException, NoSuchElementException {
        if(rowLabel == null) throw new NullPointerException("Tabella.atLabel: L'etichetta dell'indice non può essere null");
        if(colLabel == null) throw new NullPointerException("Tabella.atLabel: Il nome della colonna non può esse null");

        Colonna<V> colonna;
        try {
            colonna = columnAtLabel(colLabel);
        } catch (Exception e) {
            throw new NoSuchElementException("Tabella.atLabel: Colonna con nome " + colLabel + " non trovata");
        } 
        
        try {
            return colonna.atLabel(rowLabel);
        } catch (Exception e) {
            throw new NoSuchElementException("Tabella.atLabel: Indice con nome " + rowLabel + " non trovato");
        }
    }

    /**
     * Restituisce una nuova tabella con le colonne di this, ma con un nuovo indice.
     * 
     * EFFECTS: se newIndex è null, lancia {@code NullPointerException},
     *          se il nuovo indice è più corto dell'indice attuale, lancia {@code IllegalArgumentException},
     *          altrimenti restituisce una nuova tabella con le colonne di this, ma con un nuovo indice.
     * 
     * @param newIndex il nuovo indice.
     * @return la tabella con il nuovo indice.
     * @throws NullPointerException se newIndex è nullo.
     * @throws IllegalArgumentException se il nuovo indice è più corto dell'indice attuale.
     */
    public Tabella<V> withIndex(Indice newIndex) throws NullPointerException, IllegalArgumentException {
        if(newIndex == null) throw new NullPointerException("Tabella.withIndex: L'indice non può essere null");
        if(getIndice().length() > newIndex.length()) throw new IllegalArgumentException("Tabella.withIndex: Il nuovo indice deve essere lungo almeno quanto l'indice attuale");
        List<Colonna<V>> newColonne = new ArrayList<>();
        for (Colonna<V> colonna : colonne) {
            newColonne.add(colonna.withIndex(newIndex));
        }
        Tabella<V> newTabella= new Tabella<>(newColonne);

        return newTabella;
    }

    /**
     * restituisce una nuova tabella con indice e colonne di this, ma cambiando ciascuna intestazione di colonna con il nome specificato nella lista.
     * 
     * EFFECTS: se intestazioni è null, lancia {@code NullPointerException},
     *          se il numero di intestazioni non corrisponde al numero di colonne della tabella, lancia {@code IllegalArgumentException},
     *          altrimenti restituisce una nuova tabella con indice e colonne di this, ma cambiando ciascuna intestazione di colonna con il nome specificato nella lista.
     * 
     * @param intestazioni la lista di intestazioni da assegnare alle colonne di this
     * @throws NullPointerException se la lista di intestazioni è nulla.
     * @throws IllegalArgumentException se il numero di intestazioni non corrisponde al numero di colonne di this.
     * @return una nuova tabella con indice e colonne di this, ma intestazioni delle colonne cambiate.
     */
    public Tabella<V> withHeaders(List<String> intestazioni) throws NullPointerException, IllegalArgumentException {
        if (intestazioni == null) throw new NullPointerException("Tabella.withHeaders: Intestazioni non può essere null");
        if (intestazioni.size() != colonne.size()) throw new IllegalArgumentException("Tabella.withHeaders: Il numero di intestazioni deve essere uguale al numero di colonne");
        List<Colonna<V>> newColonne=new ArrayList<>();
        for (int i = 0; i < intestazioni.size(); i++) {
            Colonna<V> colonna = colonne.get(i);
            String intestazione= intestazioni.get(i);
            if (intestazione!=null && !intestazione.isEmpty()) newColonne.add(colonna.withName(intestazione));
            else newColonne.add(colonna);
        }
        return new Tabella<>(newColonne);
    }

    /**
     * Restituisce la tabella risultante dall'affiancamento a this un'altra tabella.
     * 
     * EFFECTS: se tabella è null, lancia {@code NullPointerException},
     *          se le due tabelle non sono dello stesso tipo, lancia {@code IllegalArgumentException},
     *          se le due tabelle hanno colonne con lo stesso nome, lancia {@code IllegalArgumentException},
     *          altrimenti restituisce la tabella risultante dall'affiancamento a this un'altra tabella, con un nuovo indice che è la fusione degli indici di entrambe le tabelle e la reindicizzazione dei valori delle colonne.
     * 
     * @param tabella la tabella da affiancare a this.
     * @return la tabella risultante dall'affiancamento a this di un'altra tabella.
     * @throws NullPointerException se la tabella da affiancare è nulla.
     * @throws IllegalArgumentException se le due tabelle non sono dello stesso tipo.
     * @throws IllegalArgumentException se le due tabelle hanno colonne con lo stesso nome.
     */
    public Tabella<V> concat(Tabella<V> tabella) throws NullPointerException, IllegalArgumentException {
        if(tabella == null) throw new NullPointerException("Tabella.concat: Tabella non può essere null");
        if(!tabella.getClass().equals(this.getClass())) throw new IllegalArgumentException("Tabella.concat: La tabella da affiancare deve essere dello stesso tipo di this: " + this.getClass().getName() + " != " + tabella.getClass().getName());
        Indice newIndex=this.getIndice().merge(tabella.getIndice());
        List<Colonna<V>> nuoveColonne = new ArrayList<>();
        for(int i=0; i<colonne.size(); i++){
            Colonna<V> colonna1 = colonne.get(i); 
            colonna1=colonna1.reindex(newIndex);
            nuoveColonne.add(colonna1);
        }
        for(Colonna<V> colonna2 : tabella.colonne) {
            nuoveColonne.add(colonna2.reindex(newIndex));
        }

        return new Tabella<>(nuoveColonne);
    }

    /**
     * Restituisce la tabella risultante dall'impilamento di this con un'altra tabella.
     * 
     * EFFECTS: se tabella è null, lancia {@code NullPointerException},
     *          se le due tabelle non sono dello stesso tipo, lancia {@code IllegalArgumentException},
     *          altrimenti restituisce la tabella risultante dall'impilamento di this con un'altra tabella, con un nuovo indice che è la fusione degli indici di entrambe le tabelle e la reindicizzazione/impilamento dei valori delle colonne.
     * 
     * @param tabella la tabella da impilare a this.
     * @return la tabella risultante dall'impilamento di this con un'altra tabella.
     * @throws NullPointerException se la tabella da impilare è nulla.
     * @throws IllegalArgumentException se le due tabelle non sono dello stesso tipo.
     */
    public Tabella<V> stack(Tabella<V> tabella) throws NullPointerException, IllegalArgumentException {
        if(tabella == null) throw new NullPointerException("Tabella.stack: La tabella non può essere null");
        if(!tabella.getClass().equals(this.getClass())) throw new IllegalArgumentException("Tabella.stack: La tabella da impilare deve essere dello stesso tipo di this: " + this.getClass().getName() + " != " + tabella.getClass().getName());
        Indice newIndex=this.getIndice().merge(tabella.getIndice());
        boolean found=false;
        List<Colonna<V>> columns = new ArrayList<>();
        for(int i=0; i<this.colonne.size(); i++){
            Colonna<V> colonna1 = this.colonne.get(i);
            for(Colonna<V> colonna2 : tabella.colonne) {
                if (colonna1.getName().equals(colonna2.getName())) {
                    found = true;
                    colonna1=colonna1.stack(colonna2);
                    columns.add(colonna1);
                    break;
                }
            }
            if (!found){
                columns.add(colonna1.reindex(newIndex));
            }
        }
        for(Colonna<V> colonna2 : tabella.colonne) {
            found=false;
            for(Colonna<V> colonna1 : columns) {
                if (colonna1.getName().equals(colonna2.getName())) {
                    found = true;
                    break;
                }
            }
            if (!found) {
                columns.add(colonna2.reindex(newIndex));
            }
        }
        return new Tabella<>(columns);
    }

    /**
     * Restituisce una nuova tabella con i valori delle colonne di this trasformati dalla funzione specificata.
     * 
     * EFFECTS: se la funzione è null, lancia {@code NullPointerException},
     *          altrimenti restituisce una nuova tabella con i valori delle colonne trasformati dalla funzione specificata. Le colonne della nuova tabella hanno lo stesso indice della tabella corrente.
     * 
     * @param <U> il tipo di valore delle colonne della nuova tabella.
     * @param function la funzione da applicare ai valori delle colonne di this.
     * @return una nuova tabella con i valori delle colonne trasformati dalla funzione specificata.
     * @throws NullPointerException se la funzione è null.
     */
    public <U> Tabella<U> map(Function<V, U> function) throws NullPointerException {
        if(function == null) throw new NullPointerException("Tabella.map: La funzione non può essere null");
        List<Colonna<U>> nuoveColonne = new ArrayList<>();
        for (Colonna<V> colonna : this.colonne) {
            Colonna<U> newColonna = colonna.map(function);
            nuoveColonne.add(newColonna);
        }
        return new Tabella<>(nuoveColonne);
    }

    /**
     * Restituisce una nuova tabella con i valori delle colonne di this moltiplicati per il numero specificato.
     * 
     * EFFECTS: se la moltiplicazione non è supportata dal tipo del valore, lancia {@code UnsupportedOperationException} 
     * restituisce una nuova tabella con i valori delle colonne moltiplicati per il numero specificato utilizzando il metodo {@link #map(Function)}.
     * 
     * {@code @SuppressWarnings("unchecked")} è sicuro perchè il cast viene sempre fatto dopo aver controllato il tipo del valore.
     * 
     * @param n il numero per cui moltiplicare i valori delle colonne della tabella corrente.
     * @return una nuova tabella con i valori delle colonne moltiplicati per il numero specificato.
     * @throws IllegalArgumentException se il tipo della tabella non è Integer.
     */
    @SuppressWarnings("unchecked")
    public Tabella<V> mul(int n) throws UnsupportedOperationException{
        return map(valore -> {
            if (valore instanceof Integer intValue) {
                return (V) (Integer) (intValue * n);
            } else if (valore instanceof Double doubleValue) {
                return (V) (Double) (doubleValue * n);
            } else if (valore instanceof Float floatValue) {
                return (V) (Float) (floatValue * n);
            } else if (valore instanceof Long longValue) {
                return (V) (Long) (longValue * n);
            } else {
                throw new UnsupportedOperationException("Tabella.mul: Moltiplicazione non applicabile per il tipo: " + valore.getClass());
            }
           });
    }
    /**
     * Restituisce una nuova tabella con un unico valore di indice, la quale contiene per ciascuna colonna dati statistici calcolati dalla funzione specificata.
     * 
     * EFFECTS: se function è null, lancia {@code NullPointerException},
     *          altrimenti, restituisce una nuova tabella con un unico valore di indice, la quale contiene per ciascuna colonna i dati statistici calcolati dalla funzione specificata.
     * 
     * @param <U> il tipo di valore delle colonne della nuova tabella.
     * @param function la funzione da applicare ai valori delle colonne di this.
     * @return una nuova tabella con un unico valore di indice, la quale contiene per ciascuna colonna i dati statistici calcolati dalla funzione specificata.
     * @throws NullPointerException se la funzione è null.
     * 
     */
    public <U> Tabella<U> mapColumn(Function<Colonna<V>, U> function) throws NullPointerException{
        if(function == null) throw new NullPointerException("Tabella.mapColumn: La funzione non può essere null");
        List<Colonna<U>> nuoveColonne = new ArrayList<>();
        List<Object> etichetta = new ArrayList<>();
        etichetta.add(0);
        Indice newIndice = new Indice(this.getIndice().getName(), etichetta);

        for (Colonna<V> colonna : colonne) {
            U risultato = function.apply(colonna);
            List<U> valori = new ArrayList<>();
            valori.add(risultato);
            @SuppressWarnings("unchecked")
            U[] valoriArray = (U[]) java.lang.reflect.Array.newInstance(risultato.getClass(), valori.size());
            valoriArray = valori.toArray(valoriArray);
            Colonna<U> newColonna = new Colonna<U>(colonna.getName(), newIndice, valoriArray);
            nuoveColonne.add(newColonna);
        }
        return new Tabella<>(nuoveColonne);
    }

    /**
     * Restituisce una tabella con un unico indice, che rappresenta la somma di tutti i valori della corrispondente colonna di this.
     * 
     * EFFECTS: se il valore non è un numero, lancia {@code UnsupportedOperationException}
     * restituisce una tabella con un unico indice, che rappresenta la somma di tutti i valori della corrispondente colonna di this.
     * 
     * @return una tabella con un unico indice, che rappresenta la somma di tutti i valori della corrispondente colonna di this.
     * @throws IllegalArgumentException se il tipo di valore della colonna non è un numero.
     */
    public Tabella<Integer> sums() throws UnsupportedOperationException {
        return this.mapColumn(col -> {
            int somma = 0;
            for (V v : col) {
                if (!(v instanceof Number)) {
                    throw new UnsupportedOperationException("Tabella.sums: Tipo di valore non supportato per la somma: " + v.getClass().getName());
                }
                somma+=(Integer) v;
            }
            return somma;
        });
    }

    @Override
    public Iterator<Colonna<V>> iterator() {
        return colonne.iterator();
    }

    @Override
    public boolean equals(Object obj) {
        if (!(obj instanceof Tabella)) return false;
        Tabella<?> other = (Tabella<?>) obj;
        return this.colonne.equals(other.colonne);
    }

    @Override
    public int hashCode() {
        return colonne.hashCode();
    }

    @Override
    public String toString(){
        String output="Tabella [\n";
        for(Colonna<V> colonna : colonne){
            output+=colonna.toString()+"\n";
        }
        output+="]";
        return output;
    }

}
