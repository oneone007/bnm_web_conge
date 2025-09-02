<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIASP Framework & Didactic Terms</title>
    <style>
        @page {
            size: A4;
            margin: 2cm;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .header .subtitle {
            color: #7f8c8d;
            font-size: 16px;
            margin-top: 10px;
            font-style: italic;
        }
        
        .section {
            margin-bottom: 40px;
            /* Allow normal flow by default; we will override for PDF export */
            page-break-inside: auto;
            break-inside: auto;
        }
        
        .section-title {
            background: #3498db;
            color: white;
            padding: 15px;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .framework-step {
            background: #ecf0f1;
            border-left: 5px solid #e74c3c;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 0 5px 5px 0;
        }
        
        .framework-step h3 {
            color: #e74c3c;
            margin-top: 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .step-number {
            background: #e74c3c;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .example-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .example-title {
            color: #d68910;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .term {
            background: white;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .term-name {
            color: #2c3e50;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 8px;
        }
        
        .term-definition {
            color: #555;
            line-height: 1.5;
        }
        
        .highlight {
            background: #f39c12;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
        
        .note {
            background: #d5f4e6;
            border-left: 4px solid #27ae60;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        
        .note-title {
            color: #27ae60;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .emoji {
            font-size: 1.2em;
        }
        
        ul {
            padding-left: 25px;
        }
        
        li {
            margin-bottom: 8px;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            
            .section { page-break-before: auto; page-break-inside: auto !important; break-inside: auto !important; margin-bottom: 16px; }
            .term, .framework-step { break-inside: avoid; page-break-inside: avoid; }
        }

        /* Temporary overrides applied only during PDF export to avoid big gaps */
        .pdf-export .section { page-break-inside: auto !important; break-inside: auto !important; margin-bottom: 16px; }
        .pdf-export .term, .pdf-export .framework-step { page-break-inside: avoid; break-inside: avoid; }
        .pdf-export .term { box-shadow: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Educational Framework Guide</h1>
        <div class="subtitle">PIASP Framework & Essential Didactic Terms for Middle School English Teachers</div>
    </div>

    <div class="section">
        <div class="section-title">
            <span class="emoji">🏫</span> PIASP FRAMEWORK FOR MIDDLE SCHOOL
        </div>
        
        <p><strong>Target Level:</strong> Middle School <span class="emoji">🎒</span><br>
        <strong>Application:</strong> Grammar Lessons<br>
        <strong>Author:</strong> Raghioui Mohamed</p>

        <div class="framework-step">
            <h3><span class="step-number">1</span> WARM UP</h3>
            <p><strong>Purpose:</strong> Introduction to the lesson</p>
            <ul>
                <li>Use images or visual aids</li>
                <li>Review previous lesson if indirectly related to current topic</li>
                <li>Activate prior knowledge</li>
                <li>Prepare students mentally for the new content</li>
            </ul>
        </div>

        <div class="framework-step">
            <h3><span class="step-number">2</span> PRESENTATION</h3>
            <p><strong>Purpose:</strong> Introduce new content through examples</p>
            <ul>
                <li>Present examples or short passages</li>
                <li>Begin the main lesson content</li>
                <li>Provide context for learning</li>
            </ul>
            
            <div class="example-box">
                <div class="example-title"><span class="emoji">📝</span> Example - Year 1 Middle School (Punctuation Lesson)</div>
                <p><strong>Sample Passage:</strong><br>
                "Hello <span class="emoji">👋</span> my name is Mohamed what is your name ??"</p>
                <p><strong>Activity:</strong> Read the passage and select pairs of students to read it aloud.</p>
            </div>
        </div>

        <div class="framework-step">
            <h3><span class="step-number">3</span> ISOLATION</h3>
            <p><strong>Purpose:</strong> Extract key elements from the text</p>
            <ul>
                <li>Go through the sentence systematically</li>
                <li>Extract and identify specific language features</li>
                <li>Focus on target grammar points</li>
            </ul>
            
            <div class="example-box">
                <div class="example-title">Example Application:</div>
                <p>From the passage, extract:<br>
                <span class="highlight">Hi + subject + ...? Question ⁉️ mark</span></p>
            </div>
        </div>

        <div class="framework-step">
            <h3><span class="step-number">4</span> ANALYSIS</h3>
            <p><strong>Purpose:</strong> Detailed examination of language features</p>
            <ul>
                <li>Analyze the sentence or passage with precision</li>
                <li>Extract all punctuation marks present</li>
                <li>Write them under the original sentence</li>
                <li>Explain each element in detail</li>
            </ul>
            
            <div class="example-box">
                <div class="example-title">Example Analysis:</div>
                <p>Take the word "Hi" and explain:<br>
                "We use <span class="highlight">capital letters 🔤</span> at the beginning of sentences"<br>
                Continue with remaining elements...</p>
            </div>
        </div>

        <div class="framework-step">
            <h3><span class="step-number">5</span> STATING RULES</h3>
            <p><strong>Purpose:</strong> Formulate grammar rules collaboratively</p>
            <ul>
                <li>Students have already heard explanations and understand concepts</li>
                <li>Students help formulate the rules</li>
                <li>Teacher guides rule formation</li>
            </ul>
            
            <div class="example-box">
                <div class="example-title">Example Rule Formation:</div>
                <p><strong>Rule:</strong> "To start a sentence, I use <span class="highlight">capital letters 🔤</span>"<br>
                Continue with all punctuation marks, with students contributing to rule creation.</p>
            </div>
        </div>

        <div class="framework-step">
            <h3><span class="step-number">6</span> PRACTICE ✍️</h3>
            <p><strong>Purpose:</strong> Apply learned concepts</p>
            <ul>
                <li>Provide only two exercises</li>
                <li>Focus on immediate application</li>
                <li>Reinforce understanding through practice</li>
            </ul>
        </div>

        <div class="note">
            <div class="note-title">Important Note:</div>
            <p>Any lesson objective is achieved in the <strong>final stage</strong> of the lesson, regardless of the lesson type (listening <span class="emoji">🎧</span>, reading, or writing).</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">
            <span class="emoji">📚</span> ESSENTIAL DIDACTIC TERMS FOR CAPEM
        </div>
        
        <p style="text-align: center; font-style: italic; margin-bottom: 30px;">
            "Essential didactic terms that an English teacher may need on CAPEM day"<br>
            <strong>Good Luck!</strong>
        </p>

        <div class="term">
            <div class="term-name">Didactic</div>
            <div class="term-definition">Teaching method focused on imparting knowledge or instruction.</div>
        </div>

        <div class="term">
            <div class="term-name">CBA (Curriculum-Based Assessment)</div>
            <div class="term-definition">Evaluating student learning based on the established curriculum.</div>
        </div>

        <div class="term">
            <div class="term-name">Motivation</div>
            <div class="term-definition">The drive or reason behind a person's actions, desires, and needs.</div>
        </div>

        <div class="term">
            <div class="term-name">Evaluation</div>
            <div class="term-definition">The process of assessing or judging the value, quality, or significance of something.</div>
        </div>

        <div class="term">
            <div class="term-name">Assessment vs. Evaluation</div>
            <div class="term-definition"><strong>Key Difference:</strong> Evaluation is making a judgment about the value of something, while assessment is the process of gathering information to make the evaluation.</div>
        </div>

        <div class="term">
            <div class="term-name">Diagnostic Assessment</div>
            <div class="term-definition">Evaluates a student's strengths, weaknesses, and knowledge before instruction.</div>
        </div>

        <div class="term">
            <div class="term-name">Summative Assessment</div>
            <div class="term-definition">Evaluates a student's learning at the end of an instructional unit.</div>
        </div>

        <div class="term">
            <div class="term-name">Cognitive Assessment</div>
            <div class="term-definition">Measures a person's cognitive abilities, such as memory, attention, and problem-solving.</div>
        </div>

        <div class="term">
            <div class="term-name">SMART Goals</div>
            <div class="term-definition">Acronym for <strong>S</strong>pecific, <strong>M</strong>easurable, <strong>A</strong>chievable, <strong>R</strong>elevant, and <strong>T</strong>ime-bound, used for setting goals.</div>
        </div>

        <div class="term">
            <div class="term-name">Learning Objective</div>
            <div class="term-definition">Clearly defined, specific, and measurable statements that describe what learners will be able to do after instruction.</div>
        </div>

        <div class="term">
            <div class="term-name">VAKT Learning Styles</div>
            <div class="term-definition">Stands for <strong>V</strong>isual, <strong>A</strong>uditory, <strong>K</strong>inesthetic, and <strong>T</strong>actile learning styles.</div>
        </div>

        <div class="term">
            <div class="term-name">Competency</div>
            <div class="term-definition">Refers to the ability to do something successfully or efficiently.</div>
        </div>

        <div class="term">
            <div class="term-name">Competence</div>
            <div class="term-definition">Essentially the same as competency, referring to the ability to do something effectively.</div>
        </div>

        <div class="term">
            <div class="term-name">Competence vs. Competency</div>
            <div class="term-definition"><strong>Key Difference:</strong> In practical use, they are often interchangeable, but "competence" is often used to describe a general ability, while "competency" can refer to specific skills within a particular context.</div>
        </div>

        <div class="term">
            <div class="term-name">Intrinsic Motivation</div>
            <div class="term-definition">Motivation that comes from within oneself, driven by personal satisfaction or enjoyment of the task itself.</div>
        </div>

        <div class="term">
            <div class="term-name">Extrinsic Motivation</div>
            <div class="term-definition">Motivation that comes from external sources, such as rewards, recognition, or avoiding punishment.</div>
        </div>
    </div>

    <div class="footer">
        <p>This document serves as a comprehensive guide for middle school English teachers<br>
        focusing on effective grammar instruction and essential pedagogical terminology.</p>
        <p><strong>Prepared by:</strong> BMK Abderrahmane & Raghioui Mohamed</p>
    </div>

    <!-- PDF libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-YcsIP8Y2dAi5o6n4R0VYjY3+P3lUrfq9mM0wM8j8X8L2rN2vKjz0Sok1O3J6tB1m3kFy1nE1Tt5mO2p9l6RZ6w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        // Add print functionality
        function printDocument() {
            window.print();
        }

        // Download PDF using html2pdf with sane defaults to prevent gaps
        async function downloadPDF() {
            const element = document.body; // Export the visible page content
            document.body.classList.add('pdf-export');

            // Ensure buttons are ignored by renderer
            const buttons = document.querySelectorAll('button');
            buttons.forEach(btn => btn.setAttribute('data-html2canvas-ignore', 'true'));

            const opt = {
                margin: [10, 10, 10, 10], // mm
                filename: 'Educational_Framework.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css', 'legacy'] }
            };

            try {
                await html2pdf().set(opt).from(element).save();
            } finally {
                document.body.classList.remove('pdf-export');
                buttons.forEach(btn => btn.removeAttribute('data-html2canvas-ignore'));
            }
        }
        
        // Add button to print (hidden in print mode)
        document.addEventListener('DOMContentLoaded', function() {
            const printBtn = document.createElement('button');
            printBtn.innerHTML = '🖨️ Print PDF';
            printBtn.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #3498db;
                color: white;
                border: none;
                padding: 12px 20px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                z-index: 1000;
            `;
            printBtn.onclick = printDocument;

            const downloadBtn = document.createElement('button');
            downloadBtn.innerHTML = '⬇️ Download PDF';
            downloadBtn.style.cssText = `
                position: fixed;
                top: 20px;
                right: 160px;
                background: #2ecc71;
                color: white;
                border: none;
                padding: 12px 20px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                z-index: 1000;
            `;
            downloadBtn.onclick = downloadPDF;
            
            // Hide button when printing
            const style = document.createElement('style');
            style.textContent = '@media print { button { display: none !important; } }';
            document.head.appendChild(style);
            
            document.body.appendChild(printBtn);
            document.body.appendChild(downloadBtn);
        });
    </script>
</body>
</html>