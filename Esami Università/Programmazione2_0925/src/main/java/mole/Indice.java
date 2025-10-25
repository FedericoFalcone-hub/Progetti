package mole;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.HashSet;
import java.util.Iterator;
import java.util.List;
import java.util.NoSuchElementException;
import java.util.Set;

/**
 * Un indice. <br>
 * 
 * Un indice è un insieme di etichette ordinate, a cui è associato un nome.
 *
 * Le etichette possono essere di tipo diverso.
 * 
 * Gli indici numerici possono essere costruiti a partire da una progressione
 * aritmetica, formata da un valore di inizio, uno finale (non incluso) e un
 * passo.
 * 
 * Gli oggetti di {@code Indice} sono immutabili.
 * 
 * Fornisce metodi che permettono di ottenere informazioni, iterare le etichette
 * e fondere 2 indici.
 * 
 */
public class Indice implements Iterable<Object> {

    /**
     * Un intervallo. <br>
     * 
     * Un intervallo è una progressione aritmetica formata da un valore iniziale,
     * uno finale (non incluso) e uno step, il quale rappresenta l'incremento (o
     * decremento) costante che separa due termini consecutivi.
     * 
     * Gli oggetti di {@code Intervallo} sono immutabili.
     * 
     * Fornisce metodi che permettono di ottenere informazioni.
     */
    private class Intervallo {

        /** Il valore iniziale dell'intervallo. */
        private final int start;
        /** Il valore finale (non incluso) dell'intervallo */
        private final int end;
        /** Lo step dell'intervallo. */
        private final int step;

        /*-
         * AF= il valore di inizio è contenuto nel campo start, il valore di fine è contenuto nel campo end e lo step è contenuto nel campo step. L'i-esimo valore si ottiene con start + i*step.
         * 
         * RI: - step != 0;
         *     - start != end;
         *     - se step > 0 allora start < end;
         *     - se step < 0 allora start > end;
         */

        /**
         * Inizializza this in modo che abbia start come valore iniziale, end come
         * valore finale e step come passo.
         * 
         * EFFECTS: se start è maggiore di end e step è positivo, lancia
         * {@code IllegalArgumentException},
         * se start è minore di end e step è negativo, lancia
         * {@code IllegalArgumentException},
         * se start è uguale a end, lancia {@code IllegalArgumentException},
         * se step è zero, lancia {@code IllegalArgumentException},
         * altrimenti inizializza this in modo che abbia start come valore iniziale, end
         * come valore finale e step come passo.
         * 
         * @param start il valore iniziale di this.
         * @param end   il valore finale di this.
         * @param step  lo step di this.
         * @throws IllegalArgumentException se start è maggiore di end e step è
         *                                  positivo, se start è minore di end e step è
         *                                  negativo, se step è zero o se step è uguale
         *                                  a end.
         */
        private Intervallo(int start, int end, int step) throws IllegalArgumentException {
            if (step > 0 && start > end)
                throw new IllegalArgumentException(
                        "Indice.Intervallo.<init>: Con step positivo il valore iniziale deve essere minore di quello finale: "
                                + start + " > " + end);
            if (step < 0 && start < end)
                throw new IllegalArgumentException(
                        "Indice.Intervallo.<init>: Con step negativo il valore iniziale deve essere maggiore di quello finale: "
                                + start + " < " + end);
            if (start == end)
                throw new IllegalArgumentException(
                        "Indice.Intervallo.<init>: Il valore iniziale non può essere uguale a quello finale: " + start
                                + " == " + end);
            if (step == 0)
                throw new IllegalArgumentException(
                        "Indice.Intervallo.<init>: Lo step deve assumere un valore diverso da 0");
            this.start = start;
            this.step = step;
            this.end = end;

        }

        /**
         * Restituisce il valore di inizio.
         * 
         * EFFECTS: Restituisce il valore di inizio.
         * 
         * @return Il valore di inizio.
         */
        private int getStart() {
            return start;
        }

        /**
         * Resituisce il valore di fine.
         * 
         * EFFECTS: Resituisce il valore di fine.
         * 
         * @return il valore di fine.
         */
        private int getEnd() {
            return end;
        }

        /**
         * Restituisce il valore dello step.
         * 
         * EFFECTS: Restituisce il valore dello step.
         * 
         * @return il valore valore dello step.
         */
        private int getStep() {
            return step;
        }

        /**
         * Restituisce il numero di valori presenti in this.
         * 
         * EFFECTS: Restituisce il numero di valori in this.
         * 
         * @return il numero di valori presenti in this.
         */
        private int length() {
            if (step > 0) {
                if (start >= end)
                    return 0;
                return (int) Math.ceil((double) (end - start) / step);
            } else {
                if (start <= end)
                    return 0;
                return (int) Math.ceil((double) (start - end) / -step);
            }

        }

        /**
         * Restituisce l'n-esimo valore di this.
         * 
         * EFFECTS: Se n maggiore della lunghezza di this o se ha un valore negativo,
         * lancia {@code IllegalArgumentException},
         * altrimenti restituisce l'n-esimo valore di this.
         * 
         * @param n la posizione del valore da cercare.
         * @return l'n-esimo valore.
         * @throws IllegalArgumentException se n è fuori dall'intervallo o se ha un
         *                                  valore negativo.
         */
        private int getValueAt(int n) throws IllegalArgumentException {
            if (n < 0)
                throw new IllegalArgumentException("Indice.Intervallo.getValueAt: n non può essere minore di 0: " + n);
            if (n > length())
                throw new IllegalArgumentException(
                        "Indice.Intervallo.getValueAt: n fuori dall'intervallo: " + n + " > " + length());
            return start + step * n;
        }

    }

    /** Il nome dell'{@code Indice}. */
    private final String nome;

    /** La lista contenente le etichette. */
    private final List<Object> etichette;

    /*-
     * AF: - il nome è contenuto nel relativo campo con lo stesso nome, mentre le etichette sono contenute nella relativa lista etichette.
     * 
     * RI: - nome!=null;
     *     - etichette!=null;
     *     - etichette.size() > 0;
     *     - ∀e ∈ etichette: e!=null;
     *     - ∀e1, e2 ∈ etichette: e1!=e2;
     *     - se etichette.get(0) è di tipo Intervallo, allora:
     *         - etichette.size() == 1;
     *         - etichette.get(0) !=null;
     */

    /**
     * Inizializza this in modo che abbia nome name e contenga tutte le etichette di
     * valori.
     * 
     * EFFECTS: Se name o valori sono null, lancia {@code NullPointerException},
     * se valori è vuoto, contiene valori nulli o duplicati, lancia
     * {@code IllegalArgumentException},
     * altrimenti inizializza this in modo che abbia nome name e contenga tutte le
     * etichette di valori.
     * 
     * @param name   il nome di this.
     * @param valori le etichette di this.
     * @throws IllegalArgumentException se valori è vuoto, se contine valori nulli o
     *                                  duplicati.
     * @throws NullPointerException     se name o valori sono null.
     */
    public Indice(String name, Object[] valori) throws IllegalArgumentException, NullPointerException {
        this(name, Arrays.asList(valori));
    }

    /**
     * Inizializza this in modo che abbia nome name e contenga tutte le etichette di
     * labels.
     * 
     * EFFECTS: Se name e labels sono null, lancia {@code NullPointerException},
     * se labels è vuoto, contiene valori nulli o duplicati, lancia
     * {@code IllegalArgumentException},
     * altrimenti inizializza this in modo che abbia nome name e contenga tutte le
     * etichette di labels.
     * 
     * @param name   il nome di this.
     * @param labels le etichette di this.
     * @throws IllegalArgumentException se labels è vuoto, contiene valori nulli o
     *                                  duplicati.
     * @throws NullPointerException     se name o labels sono null.
     */
    protected Indice(String name, List<Object> labels) throws IllegalArgumentException, NullPointerException {
        if (name == null)
            throw new NullPointerException("Indice.<init>: Il nome dell'indice non può essere null.");
        if (labels == null)
            throw new NullPointerException("Indice.<init>: Le etichette non possono essere null.");
        if (labels.isEmpty())
            throw new IllegalArgumentException("Indice.<init>: Le etichette non possono essere vuote.");
        Set<Object> set = new HashSet<>();
        this.nome = name;
        List<Object> tempLabels = new ArrayList<>();
        for (Object etichetta : labels) {
            if (etichetta == null)
                throw new IllegalArgumentException("Indice.<init>: L'indice non può avere valori nulli");
            if (set.add(etichetta)) {
                tempLabels.add(etichetta);
            } else
                throw new IllegalArgumentException(
                        "Indice.<init>: L'indice non può avere valori duplicati: " + etichetta);
        }
        this.etichette = Collections.unmodifiableList(tempLabels);
    }

    /**
     * Inizializza this in modo che abbia nome name e contenga tutte le etichette
     * della progressione aritmetica definita da start, end e step.
     * 
     * EFFECTS: se name è null, lancia {@code NullPointerException},
     * se start è maggiore di end e step è positivo, lancia
     * {@code IllegalArgumentException},
     * se start è minore di end e step è negativo, lancia
     * {@code IllegalArgumentException},
     * se step è zero, lancia {@code IllegalArgumentException},
     * altrimenti inizializza this in modo che abbia nome name e contenga tutte le
     * etichette della progressione aritmetica definita da start, end e step.
     * 
     * @param name  il nome di this.
     * @param start il valore iniziale dell'etichetta numerica.
     * @param end   il valore finale (non incluso) dell'etichetta numerica.
     * @param step  lo step dell'etichetta numerica.
     * @throws IllegalArgumentException se il nome è null, se lo step è zero, se lo
     *                                  step è negativo per un intervallo crescente
     *                                  o se lo step è positivo per un intervallo
     *                                  decrescente.
     * 
     */
    public Indice(String name, int start, int end, int step) throws IllegalArgumentException, NullPointerException {
        if (name == null)
            throw new NullPointerException("Indice(name, start, end, step): Il nome dell'indice non può essere null.");
        List<Object> temp = new ArrayList<>();
        try {
            temp.add(new Intervallo(start, end, step));
            this.etichette = Collections.unmodifiableList(temp);
        } catch (IllegalArgumentException e) {
            throw new IllegalArgumentException("Indice(name, start, end, step): " + e.getMessage());
        }
        this.nome = name;
    }

    /**
     * Inizializza this in modo che sia uguale a indice.
     * 
     * EFFECTS: se indice è null, lancia {@code NullPointerException},
     * altrimenti inizializza this in modo che abbiamo nome name e le stesse
     * etichette di indice.
     * 
     * @param indice l'indice da cui copiare le etichette.
     * @throws NullPointerException se indice è null.
     */
    protected Indice(Indice indice) throws NullPointerException {
        if (indice == null)
            throw new NullPointerException("Indice(indice): L'indice non può essere null");
        this.nome = indice.nome;
        this.etichette = Collections.unmodifiableList(indice.etichette);
    }

    /**
     * Restituisce il nome di this.
     * 
     * EFFECTS: restituisce il nome di this.
     * 
     * @return il nome di this.
     */
    public String getName() {
        return nome;
    }

    /**
     * Restituisce la lunghezza di this.
     * 
     * EFFECTS: restituisce la lunghezza di this.
     * 
     * @return la lunghezza di this.
     */
    public int length() {
        if (etichette.get(0) instanceof Intervallo intervallo) {
            return intervallo.length();
        } else {
            return etichette.size();
        }
    }

    /**
     * Restituisce true se indice è medesimo di this, false altrimenti.
     * 
     * EFFECTS: se indice è null, lancia {@code IllegalArgumentException},
     * altrimenti restituisce true se indice è medesimo di this, false altrimenti.
     * 
     * @param indice l'indice da confrontare con this.
     * @return true se gli indici sono medesimi, false altrimenti.
     * @throws IllegalArgumentException se indice è null.
     */
    public boolean same(Indice indice) {
        if (indice == null)
            throw new IllegalArgumentException("Indice.same: L'indice da confrontare non può essere null.");
        if (indice.length() != this.length())
            return false;
        for (int i = 1; i <= this.length(); i++) {
            if (!this.labelAt(i).equals(indice.labelAt(i)))
                return false;
        }
        return true;
    }

    /**
     * Restituisce l'indice risultante dalla fusione di this con indice. Se indice è
     * medesimo di this e hanno nomi diversi, restituisce this, ma con nome di
     * indice, altrimenti restituisce this.
     * 
     * EFFECTS: se indice è null, lancia {@code IllegalArgumentException},
     * se indice è medesimo di this, restituisce this, ma con nome di indice,
     * altrimenti restituisce l'indice risultante dalla fusione di this con indice.
     * 
     * @param indice l'indice da fondere in this.
     * @return l'indice risultante dalla fusione di this con indice, o un nuovo
     *         indice con lo stesso nome e le stesse etichette di this.
     * @throws NullPointerException se indice è nullo.
     */
    public Indice merge(Indice indice) throws NullPointerException {
        if (indice == null)
            throw new NullPointerException("Indice.merge: L'indice da fondere non può essere null.");
        if (same(indice)) {
            if (!nome.equals(indice.nome))
                return new Indice(indice.nome, etichette);
            else
                return this;

        }
        return new IndiceFuso(this, indice);
    }

    /**
     * Restituisce, se presente, la posizione dell'etichetta nell'indice.
     * 
     * EFFECTS: se l'etichetta è null, lancia {@code NullPointerException},
     * se l'etichetta non è presente in this, lancia {@code NoSuchElementException},
     * altrimenti, restituisce la posizione dell'etichetta nell'indice.
     * 
     * @param etichetta l'etichetta da cercare.
     * @return la posizione dell'etichetta in this.
     * @throws NullPointerException   se l'etichetta è {@code null}.
     * @throws NoSuchElementException se l'etichetta non è presente in this.
     */
    public int positionOf(Object etichetta) throws NullPointerException, NoSuchElementException {
        if (etichetta == null)
            throw new NullPointerException("Indice.positionOf: L'etichetta da cercare non può essere null");
        if (etichette.get(0) instanceof Intervallo intervallo) {
            if ((etichetta instanceof Integer val)) {
                int start = intervallo.start;
                int end = intervallo.end;
                int step = intervallo.step;
                if ((val < start || val >= end) && step > 0)
                    throw new NoSuchElementException("Indice.positionOf: L'etichetta non è presente in this");
                if ((val > start || val <= end) && step < 0)
                    throw new NoSuchElementException("Indice.positionOf: L'etichetta non è presente in this");
                int diff = -val - intervallo.getStart();
                if (diff % intervallo.getStep() != 0)
                    throw new NoSuchElementException("Indice.positionOf: L'etichetta non è presente in this");
                else {
                    return Math.abs(diff / intervallo.getStep());
                }
            } else
                throw new NoSuchElementException("Indice.positionOf: L'etichetta non è presente in this");
        } else {
            for (int i = 0; i < etichette.size(); i++) {
                if (etichette.get(i).equals(etichetta)) {
                    return i;
                }
            }
        }
        throw new NoSuchElementException("Indice.positionOf: L'etichetta non è presente in this");
    }

    /**
     * Restituisce l'etichetta alla posizione pos.
     * 
     * EFFECTS: se pos è minore o uguale a 0, lancia
     * {@code IndexOutOfBoundsException},
     * se pos è maggiore della lunghezza di this, lancia
     * {@code IndexOutOfBoundsException},
     * altrimenti restituisce l'etichetta alla posizione pos.
     * 
     * @param pos la posizione dell'etichetta da cercare (1-based).
     * @return l'etichetta alla posizione specificata.
     * @throws IndexOutOfBoundsException se la posizione è fuori dai limiti
     *                                   dell'indice.
     */
    public Object labelAt(int pos) throws IndexOutOfBoundsException {
        if (pos > length())
            throw new IndexOutOfBoundsException("Indice.labelAt:posizione fuori dai limiti: " + pos + " > " + length());
        if (pos <= 0)
            throw new IndexOutOfBoundsException(
                    "Indice.labelAt: La posizione non può essere minore o uguale a 0: " + pos);
        if (etichette.get(0) instanceof Intervallo intervallo) {
            return intervallo.getValueAt(pos - 1);
        }
        return etichette.get(pos - 1);
    }

    /**
     * Restituisce un generatore che produce gli ultimi N elementi di this.
     * 
     * EFFECTS: se n è minore di 0, lancia {@code IllegalArgumentException},
     * se n è maggiore della lunghezza di this, lancia
     * {@code IndexOutOfBoundsException},
     * altrimenti restituisce un generatore che produce gli ultimi N elementi di
     * this.
     * 
     * @param n il numero di valori.
     * @return l'iteratore sugli ultimi N elementi di this.
     * @throws IllegalArgumentException  se n è minore di 0 o maggiore della
     *                                   lunghezza di this.
     * @throws IndexOutOfBoundsException se n è maggiore della lunghezza di this.
     */
    public Iterator<Object> lastNIterator(int n) throws IllegalArgumentException, IndexOutOfBoundsException {
        if (n < 0)
            throw new IllegalArgumentException("Indice.lastNIterator: n minore di 0: " + n);
        if (n > length())
            throw new IndexOutOfBoundsException(
                    "Indice.lastNIterator: n maggiore della lunghezza dell'indice: " + n + " > " + length());
        List<Object> lista = new ArrayList<>();
        int lunghezza = length();
        for (int i = lunghezza - n + 1; i <= lunghezza; i++) {
            lista.add(labelAt(i));
        }
        return lista.iterator();
    }

    /**
     * Restituisce un generatore che produce gli elementi di this, saltando n
     * elementi alla volta.
     * 
     * EFFECTS: restituisce un generatore sugli elementi di this, saltando n
     * elementi alla volta.
     * 
     * @param jump il numero di elementi da saltare.
     * @return il generatore sugli elementi.
     * @throws IllegalArgumentException se jump è minore di 0.
     */
    public Iterator<Object> jumpIterator(int jump) throws IllegalArgumentException {
        if (jump < 0)
            throw new IllegalArgumentException(
                    "Indice.jumpIterator: Il valore del salto non può essere minore di 0: " + jump);
        if (jump == 0)
            return iterator();

        List<Object> lista = new ArrayList<>();
        for (int i = 0; i < length();) {
            Object elemento = labelAt(i);
            if (elemento != null)
                lista.add(elemento);
            int next = i + jump;
            if (next < i)
                break;
            i = jump;
        }
        return lista.iterator();
    }

    @Override
    public Iterator<Object> iterator() {
        if (etichette.get(0) instanceof Intervallo intervallo) {

            return new Iterator<Object>() {
                private int i = 0;
                private final int lunghezza = intervallo.length();

                @Override
                public boolean hasNext() {
                    return i < lunghezza;
                }

                @Override
                public Object next() {
                    if (!hasNext()) {
                        throw new NoSuchElementException("Indice.iterator.next: Non ci sono più elementi da iterare.");
                    }
                    Object value = intervallo.getValueAt(i);
                    i++;
                    return value;
                }
            };
        }
        return etichette.iterator();
    }

    @Override
    public boolean equals(Object obj) {
        if (this == obj)
            return true;
        if (!(obj instanceof Indice))
            return false;
        Indice other = (Indice) obj;
        if (!other.getName().equals(this.getName()))
            return false;
        return this.same(other);
    }

    @Override
    public int hashCode() {
        return nome.hashCode() + etichette.hashCode();
    }

    @Override
    public String toString() {
        String output= "Nome " + nome + ", Etichette: [";
        for(int i=1; i<=length();i++){
            output+=labelAt(i);
            if(i<length()) output+=", ";
        }
        output+="]";
        return output;
    }
}
