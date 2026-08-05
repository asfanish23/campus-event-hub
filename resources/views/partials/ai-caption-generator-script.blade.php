<script>
    const textArea = document.getElementById('eventDescription');
    const generateBtn = document.getElementById('generateBtn');
    const aiStatus = document.getElementById('aiStatus');
    const tweakContainer = document.getElementById('tweakContainer');
    const tweakBtns = document.querySelectorAll('.tweak-btn');

    // ============================================
    // 1. TYPING EFFECT FUNCTION (Kesan Mengetik)
    // ============================================
    function typeEffect(element, text, speed = 15) {
        let i = 0;
        element.value = "";
        
        // Add pulsing animation to border
        element.style.borderColor = '#a78bfa';
        element.style.boxShadow = '0 0 0 3px rgba(167, 139, 250, 0.1)';
        
        return new Promise((resolve) => {
            const timer = setInterval(() => {
                if (i < text.length) {
                    element.value += text.charAt(i);
                    i++;
                    element.scrollTop = element.scrollHeight;
                } else {
                    clearInterval(timer);
                    element.style.borderColor = '#d1d5db';
                    element.style.boxShadow = 'none';
                    resolve();
                }
            }, speed);
        });
    }

    // ============================================
    // 2. GENERATE DESCRIPTION (Backend Call)
    // ============================================
    generateBtn.addEventListener('click', async () => {
        const eventName = document.querySelector('[name="name"]').value;
        const category = document.querySelector('[name="category"]').value;
        const location = document.querySelector('[name="location"]').value;
        const attendees = document.querySelector('[name="expected_attendees"]').value;
        const extraDetails = textArea.value.trim();

        if (!eventName) {
            alert("Please enter Event Name first!");
            return;
        }

        // Visual feedback: disable button and change text
        generateBtn.disabled = true;
        generateBtn.style.opacity = '0.6';
        generateBtn.textContent = '⏳ Gemini is writing...';
        aiStatus.style.display = 'block';
        tweakContainer.style.display = 'none';

        try {
            const response = await fetch('{{ route("ai.generate-description") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event_name: eventName,
                    category: category,
                    location: location,
                    attendees: attendees ? parseInt(attendees) : null,
                    extra_details: extraDetails
                })
            });

            const data = await response.json();

            if (data.success) {
                await typeEffect(textArea, data.text);
                tweakContainer.style.display = 'flex';
            } else {
                alert("Error: " + (data.error || "Could not generate description"));
            }
        } catch (error) {
            console.error("Error:", error);
            alert("Something went wrong with the AI. Please try again.");
        } finally {
            generateBtn.disabled = false;
            generateBtn.style.opacity = '1';
            generateBtn.textContent = '✨ Generate with AI';
            aiStatus.style.display = 'none';
        }
    });

    // ============================================
    // 3. TWEAK BUTTONS (Ubah Gaya Deskripsi)
    // ============================================
    tweakBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const style = btn.dataset.style;
            const currentText = textArea.value;

            if (!currentText.trim()) {
                alert("Please generate a description first!");
                return;
            }

            // Visual feedback
            btn.disabled = true;
            btn.style.opacity = '0.5';
            const originalText = btn.textContent;
            btn.textContent = '⏳...';

            try {
                const response = await fetch('{{ route("ai.tweak-description") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        text: currentText,
                        style: style
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await typeEffect(textArea, data.text);
                } else {
                    alert("Error: " + (data.error || "Could not tweak description"));
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Something went wrong. Please try again.");
            } finally {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.textContent = originalText;
            }
        });
    });

    // ============================================
    // 4. ADD CSRF TOKEN TO HEAD IF NOT EXISTS
    // ============================================
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }

    // ============================================
    // 5. ENHANCED FORM STYLING
    // ============================================
    textArea.addEventListener('focus', function() {
        this.style.borderColor = '#9333ea';
        this.style.boxShadow = '0 0 0 3px rgba(147, 51, 234, 0.1)';
    });

    textArea.addEventListener('blur', function() {
        this.style.borderColor = '#d1d5db';
        this.style.boxShadow = 'none';
    });

    // Hover effects for tweak buttons
    tweakBtns.forEach(btn => {
        btn.addEventListener('mouseover', function() {
            this.style.background = '#f3f4f6';
            this.style.borderColor = '#9333ea';
        });
        
        btn.addEventListener('mouseout', function() {
            this.style.background = 'white';
            this.style.borderColor = '#ddd';
        });
    });
</script>
