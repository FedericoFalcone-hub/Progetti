package mole;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.NoSuchElementException;
import java.util.function.Function;

/**
 * Una colonna. <br>
 * 
 * Una colonna è un insieme di valori, ciascuno dei quali associato ad un'etichetta di un {@link Indice}.
 * 
 * I valori possono essere di un unico tipo.
 * 
 * Una {@code Colonna} è caratterizzata da un nome, dal suo indice e dai suoi valori.
 * 
 * Gli oggetti di tipo {@code Colonna} sono immutabili.
 * 
 * @param <V> il tipo della colonna.
 * 
 * Fornisce metodi che permettono di ottenere informazioni, iterare sui valori, reindicizzare, impilare due colonne e mappare i valori.
 */
public class Colonna<V> implements Iterable<V> {
    
    /**L'indice della colonna.*/
    private final Indice indice;

    /**Il nome della colonna.*/
    private final String nome;

    /**La lista contente i valori della colonna.*/
    private final List<V> valori;

    /*-
     * AF: - il nome è contenuto nel relativo campo con lo stesso nome, l'indice è contenuto nel relativo campo con lo stesso nome, mentre i valori sono contenuti nella relativa lista valori.
     * 
     * RI: - nome != null;
     *     - indice != null;
     *     - valori != null;
     *     - valori.size() > 0;
     *     - valori.size() == indice.length();
     * 
     */

    /**
     * Inizializza this in modo che abbia nome nome, indice indice e contenga tutti i valori di valori.
     * 
     * EFFECTS: Se nome, valori o l'indice sono null, lancia {@code NullPointerException},
     *          se valori è vuoto lancia {@code IllegalArgumentException},
     *          se l'indice è più corto dei valori lancia {@code IllegalArgumentException},
     *          altrimenti inizializza this in modo che abbia nome nome, indice indice e contenga tutti i valori di valori. 
     * 
     * @param nome il nome della colonna.
     * @param indice l'indice della colonna.
     * @param valori i valori della colonna associati all'indice.
     * @throws NullPointerException se il nome,la lista di valori o l'indice sono {@code null}
     * @throws IllegalArgumentException se l'indice è più corto della lista dei valori o se la lista di valori è vuota.
     */
    
    public Colonna(String nome, Indice indice, V[] valori) throws NullPointerException, IllegalArgumentException {
        this(nome,indice,Arrays.asList(valori));
    }


    /**
     * Inizializza this in modo che abbia nome nome, contenga tutti i valori di valori e come indice utilizzi un indice numerico di lunghezza pari al numero di valori.
     * 
     * EFFECTS: se nome,valori sono null, lancia {@code NullPointerException},
     *          se valori è vuoto lancia {@code IllegalArgumentException},
     *          altrimenti inizializza this in modo che abbia nome nome, contenga tutti i valori di valori e come indice utilizzi un indice numerico di lunghezza pari al numero di valori.
     * 
     * @param nome il nome della colonna.
     * @param valori i valori della colonna.
     * @throws NullPointerException se il nome o la lista di valori sono {@code null}.
     * @throws IllegalArgumentException se la lista di valori è vuota.
     */
    public Colonna(String nome, V[] valori) throws NullPointerException, IllegalArgumentException {
        this(nome, new Indice("",0, valori.length,1), Arrays.asList(valori));
    }

    /**
     * Inizializza this in modo che abbia nome nome, indice indice e contenga tutti i valori di valori.
     * 
     * EFFECTS: Se nome, valori o l'indice sono null, lancia {@code NullPointerException},
     *          se valori è vuoto lancia {@code IllegalArgumentException},
     *          se l'indice è più corto dei valori lancia {@code IllegalArgumentException},
     *          altrimenti inizializza this in modo che abbia nome nome, indice indice e contenga tutti i valori di valori.
     * 
     * @param nome il nome della colonna.
     * @param indice l'indice della colonna.
     * @param valori i valori della colonna.
     * @throws NullPointerException se il nome, l'indice o i valori sono null.
     * @throws IllegalArgumentException se i valori sono vuoti o se l'indice è più corto dei valori.
     */
    private Colonna(String nome, Indice indice, List<V> valori) throws NullPointerException, IllegalArgumentException {
        if (nome == null) throw new NullPointerException("Colonna.<init>: Il nome della colonna non può essere nullo");
        if (valori==null) throw new NullPointerException("Colonna.<init>: I valori della colonna non possono essere nulli");
        if (valori.isEmpty()) throw new IllegalArgumentException("Colonna.<init>: La colonna non può essere vuota"); 
        if (indice==null) throw new NullPointerException("Colonna.<init>: L'indice non può essere nullo");
        if (indice.length() < valori.size()) throw new IllegalArgumentException("Colonna.<init>: L'indice non può essere più corto dei valori della colonna" + indice.length() + " < " + valori.size());
       
        this.nome = nome;
        this.indice = indice;
        this.valori = Collections.unmodifiableList(valori);
    }
    

    /**
     * Mappa i valori di this usando una funzione.
     * 
     * EFFECTS: se la funzione è null, lancia {@code NullPointerException},
     *          se la funzione non è supportata per il tipo dei valori di this, lancia {@code UnsupportedOperationException}, 
     *          altrimenti applica la funzione func a ogni valore di this e restituisce una nuova colonna con i valori mappati.
     * 
     * @param <U> il tipo dei valori di this risultante.
     * @param func la funzione da applicare ai valori di this.
     * @return  una nuova colonna con i valori mappati.
     * @throws NullPointerException se la funzione è null.
     * @throws UnsupportedOperationException se la funzione non è supportata per il tipo dei valori di this.
     * 
     */
    protected <U> Colonna<U> map(Function<V,U> func) throws NullPointerException, UnsupportedOperationException {
        if (func == null) throw new NullPointerException("Colonna.map: La funzione di mapping non può essere nulla");
        List<U> mappedValues = new ArrayList<>();
        for (int i = 0; i < this.valori.size(); i++) {
            try {
                mappedValues.add(func.apply(this.valori.get(i)));
            } catch (Exception e) {
                throw new UnsupportedOperationException("Colonna.map: La funzione di mapping non è supportata per il tipo: " + this.valori.get(i).getClass());
            }
        }
        Colonna<U> risultato = new Colonna<>(this.nome, this.indice, mappedValues);
        return risultato;
    }
    
    /**
     * Restituisce una collezione non modificabile dei valori di this.
     * 
     * EFFECTS: restituisce una collezione non modificabile dei valori di this.
     * 
     * @return una collezione non modificabile dei valori di this.
     */
    public java.util.Collection<Object> getValori() {
        return Collections.unmodifiableCollection(java.util.Arrays.asList(valori));
    }

    /**
     * Restituisce l'indice della colonna.
     * 
     * EFFECTS: restituisce l'indice della colonna.
     * 
     * @return l'indice della colonna.
     */
    public Indice getIndice() {
        return indice;
    }   

    /**
     * Restituisce il nome della colonna.
     * 
     * EFFECTS: restituisce il nome della colonna.
     * 
     * @return il nome della colonna.
     */
    public String getName() {
        return nome;
    }

    /**
     * Restituisce il valore associato a all'etichetta.
     * 
     * EFFECTS: se etichetta è null, lancia {@code NullPointerException},
     *          se l'etichetta non è presente nell'indice, lancia {@code NoSuchElementException},
     *          altrimenti restituisce il valore associato all'etichetta.
     * 
     * @param etichetta l'etichetta di cui cercare il valore.
     * @return il valore associato all'etichetta.
     * @throws NullPointerException se l'etichetta è null.
     * @throws NoSuchElementException se l'etichetta non è presente nell'indice.
     * 
     */
    public V atLabel(Object etichetta) throws NullPointerException, NoSuchElementException {
        if (etichetta == null) throw new NullPointerException("Colonna.atLabel: L'etichetta non può essere nulla");
        try {
            return valori.get(indice.positionOf(etichetta));
        } catch (Exception e) {
            throw new NoSuchElementException("Colonna.atLabel: Etichetta non presente nell'indice: " + etichetta);
        }
    }

    /**
     * Restituisce il valore associato alla posizione in this.
     * 
     * EFFECTS: se pos è minore di 0, lancia {@code IndexOutOfBoundsException},
     *          se pos è maggiore della lunghezza della colonna, lancia {@code IndexOutOfBoundsException},
     *          altrimenti restituisce il valore associato alla posizione pos nell'indice della colonna.
     * 
     * @param pos la posizione di cui ricavare il valore (1-based).
     * @return il valore associato alla posizione nell'indice della colonna.
     * @throws IndexOutOfBoundsException se la posizione è minore di 1 oppure se è maggiore della lunghezza di this.
     * 
     */
    public V atPosition(int pos) throws IndexOutOfBoundsException {
        if (pos < 1 ) {
            throw new IndexOutOfBoundsException("Colonna.atPosition: Posizione non valida: " + pos);
        }
        if(pos>length()) throw new IndexOutOfBoundsException("Colonna.atPosition: Posizione non valida: " + pos + ">" + length());
        return valori.get(pos-1);
    }

    /**
     * Restituisce la lunghezza di this.
     * 
     * EFFECTS: restituisce la lunghezza di this.
     * 
     * @return la lunghezza della colonna.
     */
    public int length() {
        return indice.length();
    }

    /**
     * Restituisce una nuova colonna con lo stesso nome e valori di this, ma con un nuovo indice.
     * 
     * EFFECTS: se il nuovo indice è null, lancia {@code NullPointerException},
     *          se il nuovo indice è più corto dell'indice di this, lancia {@code IllegalArgumentException},
     *          altrimenti restituisce una nuova colonna con lo stesso nome e valori di this, ma con un nuovo indice.
     * 
     * @param newIndice l'indice della nuova colonna.
     * @return una nuova colonna con lo stesso nome e valori di this, ma con un nuovo indice.
     * @throws NullPointerException se il nuovo indice è {@code null}.
     */
    public Colonna<V> withIndex(Indice newIndice) throws NullPointerException, IllegalArgumentException {
        if (newIndice==null) throw new NullPointerException("Colonna.withIndex: L'indice non può essere nullo");
        if (newIndice.length() < this.indice.length()) {
            throw new IllegalArgumentException("Colonna.withIndex: Il nuovo indice non può essere più corto dell'indice originale: " + newIndice.length() + " < " + this.indice.length());
        }
        return new Colonna<>(this.nome, newIndice, this.valori);
    }

    /**
     * Restituisce una nuova colonna con stesso indice e valori di this, ma con un nuovo nome.
     * 
     * EFFECTS: se il nuovo nome è null, lancia {@code NullPointerException},
     *          altrimenti restituisce una nuova colonna con stesso indice e valori di this, ma con un nuovo nome.
     * 
     * @param newName il nuovo nome della colonna.
     * @return una nuova colonna con il nome specificato.
     * @throws NullPointerException se il nuovo nome è {@code null}.
     */
    public Colonna<V> withName(String newName) throws NullPointerException {
        if (newName==null) throw new NullPointerException("Colonna.withName: Il nome non può essere nullo");
        return new Colonna<>(newName, this.indice, this.valori);
    }

    /**
     * Restituisce una colonna che ha lo stesso nome di this e valori di this reindicizzati secondo un nuovo indice. Se un'etichetta dell'indice di this è presente in quello nuovo, allora il valore della colonna sarà quello associato a quell'etichetta, altrimenti il valore sarà {@code null}.
     * 
     * EFFECTS: se newIndice è null, lancia {@code NullPointerException},
     *          se newIndice è più corto dell'indice di this, lancia {@code IllegalArgumentException},
     *          altrimenti restituisce una nuova colonna con lo stesso nome di this e valori reindicizzati secondo newIndice.
     * 
     * @param newIndice l'indice della nuova colonna su cui reindicizzare i valori della colonna originale.
     * @return la nuova colonna con i valori reindicizzati.
     * @throws NullPointerException se l'indice passato come parametro è {@code null}.
     * @throws IllegalArgumentException se il nuovo indice è più corto dell'indice originale.
     * 
     */
    public Colonna<V> reindex(Indice newIndice) throws NullPointerException, IllegalArgumentException {
        if(newIndice ==null) throw new NullPointerException("Colonna.reindex: L'indice non può essere nullo");
        if(newIndice.length()< this.indice.length()) throw new IllegalArgumentException("Colonna.reindex: Il nuovo indice non può essere più corto dell'indice originale: " + newIndice.length() + " < " + this.indice.length());
        List<V> newValori=new ArrayList<>();
        for (Object val : newIndice) {
            try {
                newValori.add(atLabel(val));
            } catch (Exception e) {
                newValori.add(null);
            }
        }
        Colonna<V> colonnaReindicizzata=new Colonna<>(this.nome,newIndice, newValori);
        return colonnaReindicizzata;
    }

    /**
     * Restituisce una colonna creata impilando sotto this colonna.
     * 
     * EFFECTS: se colonna è null, lancia {@code NullPointerException},
     *          se colonna non è dello stesso tipo di this, lancia {@code IllegalArgumentException},
     *          altrimenti restituisce una colonna con lo stesso nome di this, un nuovo indice che è la fusione degli indici di this e colonna, e i valori di this e colonna impilati uno sotto l'altro.
     * 
     * @param colonna la colonna da impilare sotto la colonna corrente.
     * @return la colonna risultante dall'impilamento delle due colonne.
     * @throws NullPointerException se la colonna da impilare è {@code null}.
     * @throws IllegalArgumentException se la colonna da impilare non è dello stesso tipo della colonna corrente.
     */
    public Colonna<V> stack(Colonna<V> colonna) throws NullPointerException, IllegalArgumentException {
        if (colonna==null) throw new NullPointerException("Colonna.stack: La colonna da impilare non può essere nulla");
        if(!colonna.getClass().isAssignableFrom(this.getClass())) throw new IllegalArgumentException("Colonna.stack: La colonna da impilare deve essere dello stesso tipo della colonna corrente");
        Colonna<V> nuovaColonna;
        Indice indice2= colonna.getIndice();
        Indice newIndice;
        newIndice = this.indice.merge(indice2);
        List<V> values=new ArrayList<>(this.valori);
        values.addAll(colonna.valori);
        nuovaColonna = new Colonna<>(this.nome, newIndice, values);
        return nuovaColonna;
    }

    /**
     * Restituisce una colonna con i valori di this moltiplicati per un numero intero.
     * 
     * EFFECTS: se i valori della colonna non sono numerici, lancia {@code UnsupportedOperationException},
     *          altrimenti restituisce una nuova colonna con i valori di this moltiplicati per il numero intero n.
     * 
     * {@code @SuppressWarnings("unchecked")} è sicuro perchè il cast viene sempre fatto dopo aver controllato il tipo del valore.
     * 
     * @param n il valore intero con cui moltiplicare i valori della colonna.
     * @return la colonna con i valori moltiplicati.
     * @throws UnsupportedOperationException se i valori della colonna non sono numerici.
     */
    @SuppressWarnings("unchecked")
    public Colonna<V> mul(int n) throws UnsupportedOperationException {
        return this.map(val -> {
            if (val instanceof Integer intValue) {
                return (V) (Integer) (intValue * n);
            } else if (val instanceof Double doubleValue) {
                return (V) (Double) (doubleValue * n);
            } else if (val instanceof Float floatValue) {
                return (V) (Float) (floatValue * n);
            } else if (val instanceof Long longValue) {
                return (V) (Long) (longValue * n);
            } else {
                throw new UnsupportedOperationException("Colonna.mul: Moltiplicazione non applicabile per il tipo: " + val.getClass());
            }
        });
    }

    /**
     * Restituisce una colonna cambiando formato dei valori della colonna da LocalDateTime a LocalTime della colonna originale.
     * 
     * EFFECTS: se i valori della colonna non sono di tipo LocalDateTime, lancia {@code UnsupportedOperationException},
     *          altrimenti restituisce una nuova colonna con i valori convertiti in LocalTime.
     * 
     * {@code @SuppressWarnings("unchecked")} è sicuro perchè il cast viene sempre fatto dopo aver controllato il tipo del valore.
     * 
     * @return la colonna con i valori convertiti in LocalTime.
     * @throws UnsupportedOperationException se i valori della colonna non sono di tipo LocalDateTime.
     */
    @SuppressWarnings("unchecked")
    public Colonna<V> changeTimeFormat() throws UnsupportedOperationException {
        return this.map(val -> {
            if (val instanceof LocalDateTime localDateTime) {
                return (V) localDateTime.toLocalTime();
            } else {
                throw new UnsupportedOperationException("Colonna.changeTimeFormat: Cambio di formato di tempo non applicabile per il tipo: " + val.getClass());
            }
        });
    }

    @Override
    public Iterator<V> iterator() {
        return valori.iterator();
    }
    
    @Override
    public boolean equals(Object obj) {
        if (!(obj instanceof Colonna)) return false;
        Colonna<?> other = (Colonna<?>) obj;
        return this.nome.equals(other.nome) && this.indice.equals(other.indice) && this.valori.equals(other.valori);
    }

    @Override
    public int hashCode() {
        int result = nome.hashCode();
        result = 31 * result + indice.hashCode();
        result = 31 * result + valori.hashCode();
        return result;
    }

    @Override
    public String toString() {
        return "Colonna{" +
                "nome='" + nome + '\'' +
                ", indice=" + indice +
                ", valori=" + valori +
                '}';
    }
    
}
