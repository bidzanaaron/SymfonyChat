class CallStack {
    constructor(delay) {
        this.delay = delay; // Zeitliche Verzögerung in Millisekunden
        this.stack = [];
        this.isProcessing = false;
    }

    // Fügt einen Funktionsaufruf zum Stack hinzu
    add(func) {
        this.stack.push(func);
        if (!this.isProcessing) {
            this.processStack();
        }
    }

    // Verarbeitet den Stack
    processStack() {
        if (this.stack.length === 0) {
            this.isProcessing = false;
            return;
        }

        this.isProcessing = true;
        const func = this.stack.shift(); // Entfernt die erste Funktion vom Stack
        func(); // Führt die Funktion aus

        // Wartet für die festgelegte Verzögerung, bevor die nächste Funktion verarbeitet wird
        setTimeout(() => this.processStack(), this.delay);
    }
}