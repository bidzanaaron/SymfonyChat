class CallStack {
    constructor(initialDelay) {
        this.initialDelay = initialDelay; // Anfangsverzögerung in Millisekunden
        this.delay = initialDelay;        // Aktuelle Verzögerung, beginnt mit Anfangsverzögerung
        this.stack = [];
        this.isProcessing = false;
        this.callCounter = 0;             // Zählt die Anzahl der hinzugefügten Funktionsaufrufe
        this.maxStackSize = 50;           // Maximale Stack-Größe
    }

    // Fügt einen Funktionsaufruf zum Stack hinzu
    add(func) {
        if (this.stack.length >= this.maxStackSize) {
            // Leert den Stack und setzt die Verzögerung zurück, wenn die maximale Größe erreicht ist
            this.stack = [];
            this.delay = this.initialDelay;
            this.callCounter = 0;
            console.warn('Stack-Überlauf: Alle Funktionsaufrufe wurden gelöscht.');
            return;
        }

        this.stack.push(func);
        this.callCounter++;

        // Erhöht die Verzögerung periodisch basierend auf der Anzahl der Aufrufe
        if (this.callCounter % 10 === 0) {
            this.delay = Math.min(this.delay + 1000, 4000); // Erhöht die Verzögerung, maximal jedoch 4 Sekunden
        }

        if (!this.isProcessing) {
            this.processStack();
        }
    }

    // Verarbeitet den Stack
    processStack() {
        if (this.stack.length === 0) {
            this.isProcessing = false;
            this.callCounter = 0; // Setzt den Zähler zurück, wenn der Stack leer ist
            this.delay = this.initialDelay; // Setzt die Verzögerung zurück, wenn der Stack leer ist
            return;
        }

        this.isProcessing = true;
        const func = this.stack.shift(); // Entfernt die erste Funktion vom Stack
        func(); // Führt die Funktion aus

        // Wartet für die festgelegte Verzögerung, bevor die nächste Funktion verarbeitet wird
        setTimeout(() => this.processStack(), this.delay);
    }
}