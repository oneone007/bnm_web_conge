#!/usr/bin/env python3
"""
System Security Alert - Critical Virus Detection
Advanced malware detected on system - immediate action required
Emergency containment protocol activated
"""

import tkinter as tk
import random
import time
import threading
from tkinter import messagebox
import sys

class FakeVirusPrank:
    def __init__(self):
        self.root = tk.Tk()
        self.root.title("CRITICAL SECURITY ALERT")
        
        # Make window completely unescapable
        self.root.attributes('-fullscreen', True)
        self.root.attributes('-topmost', True)  # Always on top
        self.root.configure(bg='black')
        
        # Disable window manager functions
        self.root.protocol("WM_DELETE_WINDOW", self.disable_event)
        self.root.overrideredirect(True)  # Remove window decorations
        
        # Bind all possible escape keys to disable them
        self.bind_all_keys()
        
        # Get screen dimensions
        self.width = self.root.winfo_screenwidth()
        self.height = self.root.winfo_screenheight()
        
        # Create canvas for effects
        self.canvas = tk.Canvas(self.root, width=self.width, height=self.height, bg='black')
        self.canvas.pack()
        
        self.running = True
        self.effects_active = True
        self.typed_text = ""
        self.exit_phrase = "im sorry"
        
        # Create input field
        self.create_input_field()
        
        # Focus capture loop to prevent Alt+Tab, Win+R, etc.
        self.focus_capture()
    
    def disable_event(self, event=None):
        """Disable window close events"""
        pass
    
    def bind_all_keys(self):
        """Bind all dangerous key combinations to disable them"""
        # Disable Alt+F4
        self.root.bind('<Alt-F4>', self.disable_event)
        
        # Disable Ctrl+Alt+Del (as much as possible)
        self.root.bind('<Control-Alt-Delete>', self.disable_event)
        
        # Disable Alt+Tab
        self.root.bind('<Alt-Tab>', self.disable_event)
        self.root.bind('<Alt-Shift-Tab>', self.disable_event)
        
        # Disable Windows key combinations
        self.root.bind('<Control-Escape>', self.disable_event)  # Ctrl+Esc
        self.root.bind('<Super_L>', self.disable_event)  # Left Windows key
        self.root.bind('<Super_R>', self.disable_event)  # Right Windows key
        
        # Disable function keys
        for i in range(1, 13):
            self.root.bind(f'<F{i}>', self.disable_event)
            self.root.bind(f'<Alt-F{i}>', self.disable_event)
            self.root.bind(f'<Control-F{i}>', self.disable_event)
        
        # Disable Escape key
        self.root.bind('<Escape>', self.disable_event)
        
        # Disable Ctrl+C, Ctrl+V, Ctrl+Z, etc.
        for key in ['c', 'v', 'z', 'x', 'a', 's', 'n', 'o', 'p', 'w', 'q', 't', 'r']:
            self.root.bind(f'<Control-{key}>', self.disable_event)
            self.root.bind(f'<Control-{key.upper()}>', self.disable_event)
        
        # Disable Ctrl+Shift+Esc (Task Manager)
        self.root.bind('<Control-Shift-Escape>', self.disable_event)
        
        # Disable Ctrl+Alt+T (Terminal in Linux)
        self.root.bind('<Control-Alt-t>', self.disable_event)
        self.root.bind('<Control-Alt-T>', self.disable_event)
        
        # Only allow letters for typing "im sorry"
        allowed_chars = set('im sorry')
        self.root.bind('<KeyPress>', lambda e: self.filter_keypress(e, allowed_chars))
    
    def filter_keypress(self, event, allowed_chars):
        """Only allow specific characters needed for 'im sorry'"""
        if event.char.lower() in allowed_chars or event.keysym in ['Return', 'BackSpace', 'space']:
            # Allow the key if it's needed for "im sorry"
            return
        else:
            # Block all other keys
            return "break"
    
    def focus_capture(self):
        """Continuously capture focus to prevent escaping"""
        def keep_focus():
            while self.running:
                try:
                    self.root.focus_force()
                    self.root.grab_set_global()  # Global grab - prevents all other windows
                    self.root.lift()  # Bring to front
                    self.root.attributes('-topmost', True)  # Ensure always on top
                    time.sleep(0.05)  # More frequent checks
                except:
                    pass
        
        threading.Thread(target=keep_focus, daemon=True).start()
        
    def start_prank(self):
        """Start the prank sequence"""
        # Show initial warning
        self.show_fake_warning()
        
        # Start visual effects
        threading.Thread(target=self.color_effects, daemon=True).start()
        threading.Thread(target=self.matrix_effect, daemon=True).start()
        threading.Thread(target=self.fake_loading, daemon=True).start()
        
        # Show popup warnings
        threading.Thread(target=self.popup_warnings, daemon=True).start()
        
        # Show hints about how to exit
        threading.Thread(target=self.show_exit_hints, daemon=True).start()
        
        # Anti-close protection
        threading.Thread(target=self.anti_close_protection, daemon=True).start()
        
        self.root.mainloop()
    
    def create_input_field(self):
        """Create a visible input field for the user"""
        # Create frame for input area
        self.input_frame = tk.Frame(self.root, bg='black')
        self.input_frame.place(relx=0.5, rely=0.85, anchor='center')
        
        # Create label
        self.input_label = tk.Label(self.input_frame, 
                                  text='EMERGENCY: Type "Im sorry" to unlock system:', 
                                  font=('Arial', 14, 'bold'), 
                                  fg='red', bg='black')
        self.input_label.pack(pady=5)
        
        # Create entry widget
        self.entry = tk.Entry(self.input_frame, 
                            font=('Arial', 16), 
                            width=30, 
                            bg='white', 
                            fg='black',
                            justify='center')
        self.entry.pack(pady=5)
        
        # Bind entry events
        self.entry.bind('<KeyRelease>', self.on_entry_change)
        self.entry.bind('<Return>', self.on_enter_pressed)
        
        # Focus on the entry field and keep it focused
        self.entry.focus_set()
        self.keep_entry_focused()
        
        # Create status label
        self.status_label = tk.Label(self.input_frame, 
                                   text='System locked! Authorization required!', 
                                   font=('Arial', 12), 
                                   fg='red', bg='black')
        self.status_label.pack(pady=5)
    
    def keep_entry_focused(self):
        """Keep the entry field focused at all times"""
        def focus_loop():
            while self.running:
                try:
                    self.entry.focus_set()
                    time.sleep(0.1)
                except:
                    pass
        
        threading.Thread(target=focus_loop, daemon=True).start()
    
    def show_fake_warning(self):
        """Display critical security warning"""
        warning_text = """
        🚨 CRITICAL SECURITY BREACH 🚨
        
        TROJAN DETECTED: W32.SystemHack.exe
        
        System Status: COMPROMISED
        Files Encrypted: 99.9%
        Data Transmission: IN PROGRESS
        
        IMMEDIATE ACTION REQUIRED
        System will be permanently locked in 60 seconds
        
        Contact system administrator immediately!
        """
        
        label = tk.Label(self.root, text=warning_text, font=('Arial', 20, 'bold'), 
                        fg='red', bg='black', justify='center')
        label.place(relx=0.5, rely=0.3, anchor='center')
        
        # Make it blink
        self.blink_label(label)
    
    def blink_label(self, label):
        """Make label blink"""
        def blink():
            while self.running:
                try:
                    label.config(fg='red')
                    time.sleep(0.5)
                    label.config(fg='yellow')
                    time.sleep(0.5)
                except:
                    break
        
        threading.Thread(target=blink, daemon=True).start()
    
    def color_effects(self):
        """Create colorful screen effects"""
        colors = ['red', 'green', 'blue', 'yellow', 'purple', 'orange', 'cyan', 'magenta']
        
        while self.effects_active and self.running:
            try:
                # Random colored rectangles
                for _ in range(10):
                    x1 = random.randint(0, self.width)
                    y1 = random.randint(0, self.height)
                    x2 = x1 + random.randint(50, 200)
                    y2 = y1 + random.randint(50, 200)
                    color = random.choice(colors)
                    
                    rect = self.canvas.create_rectangle(x1, y1, x2, y2, 
                                                      fill=color, outline=color)
                    
                    # Remove after short time
                    self.root.after(500, lambda r=rect: self.canvas.delete(r))
                
                time.sleep(0.1)
            except:
                break
    
    def matrix_effect(self):
        """Create Matrix-like falling text effect"""
        chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*"
        
        while self.effects_active and self.running:
            try:
                for _ in range(5):
                    x = random.randint(0, self.width)
                    y = random.randint(0, 100)
                    char = random.choice(chars)
                    color = random.choice(['green', 'lime', 'darkgreen'])
                    
                    text = self.canvas.create_text(x, y, text=char, 
                                                 fill=color, font=('Courier', 12))
                    
                    # Animate falling
                    self.animate_fall(text, y)
                
                time.sleep(0.2)
            except:
                break
    
    def animate_fall(self, text_obj, start_y):
        """Animate falling text"""
        def fall():
            y = start_y
            while y < self.height + 50 and self.running:
                try:
                    self.canvas.coords(text_obj, self.canvas.coords(text_obj)[0], y)
                    y += 5
                    time.sleep(0.05)
                except:
                    break
            try:
                self.canvas.delete(text_obj)
            except:
                pass
        
        threading.Thread(target=fall, daemon=True).start()
    
    def fake_loading(self):
        """Show fake loading progress"""
        time.sleep(3)  # Wait a bit before showing
        
        if not self.running:
            return
            
        progress_label = tk.Label(self.root, text="Initializing virus... 0%", 
                                font=('Arial', 16), fg='white', bg='black')
        progress_label.place(relx=0.5, rely=0.7, anchor='center')
        
        fake_processes = [
            "Scanning system vulnerabilities...",
            "Encrypting user documents...",
            "Harvesting login credentials...",
            "Transmitting data to remote server...",
            "Installing persistent backdoor...",
            "Disabling security software...",
            "Corrupting system registry...",
            "SYSTEM BREACH COMPLETE!"
        ]
        
        for i, process in enumerate(fake_processes):
            if not self.running:
                break
                
            progress = min(100, (i + 1) * 12)
            progress_label.config(text=f"{process} {progress}%")
            time.sleep(2)
        
        # Final message
        if self.running:
            progress_label.config(text='BREACH COMPLETE! System compromised! �', fg='red')
    
    def popup_warnings(self):
        """Show critical security alerts"""
        warnings = [
            "CRITICAL: Trojan detected!",
            "ALERT: Unauthorized access detected!",
            "ERROR: System firewall breached!",
            "WARNING: Data theft in progress!",
            "EMERGENCY: System lockdown initiated!",
            "CRITICAL: Contact IT immediately!"
        ]
        
        time.sleep(5)  # Wait before starting popups
        
        for warning in warnings:
            if not self.running:
                break
                
            try:
                # Create a small popup window
                popup = tk.Toplevel(self.root)
                popup.title("SECURITY ALERT")
                popup.geometry("300x100")
                popup.configure(bg='red')
                
                label = tk.Label(popup, text=warning, font=('Arial', 12, 'bold'),
                               fg='white', bg='red', wraplength=280)
                label.pack(expand=True)
                
                # Auto close after 3 seconds
                popup.after(3000, popup.destroy)
                
                # Position randomly
                x = random.randint(100, self.width - 400)
                y = random.randint(100, self.height - 200)
                popup.geometry(f"300x100+{x}+{y}")
                
            except:
                pass
            
            time.sleep(3)
    
    def show_exit_hints(self):
        """Show cryptic system messages"""
        hints = [
            "System requires authorization code...",
            "Access denied. Authentication needed...",
            "Emergency protocol: Express remorse...",
            "Hint: Apologize to unlock system...",
            "FINAL WARNING: Say you're sorry!"
        ]
        
        time.sleep(15)  # Wait before showing first hint
        
        for hint in hints:
            if not self.running:
                break
                
            # Show hint in a small popup
            try:
                hint_popup = tk.Toplevel(self.root)
                hint_popup.title("⚠️ SYSTEM MESSAGE")
                hint_popup.geometry("250x80")
                hint_popup.configure(bg='orange')
                
                label = tk.Label(hint_popup, text=hint, font=('Arial', 10, 'bold'),
                               fg='black', bg='orange', wraplength=230)
                label.pack(expand=True)
                
                # Auto close after 4 seconds
                hint_popup.after(4000, hint_popup.destroy)
                
                # Position in top center
                x = (self.width - 250) // 2
                y = 50
                hint_popup.geometry(f"250x80+{x}+{y}")
                
            except:
                pass
            
            time.sleep(10)  # Wait 10 seconds between hints
    
    def anti_close_protection(self):
        """Prevent the program from being closed by system methods"""
        def protection_loop():
            while self.running:
                try:
                    # Ensure window properties are maintained
                    self.root.attributes('-topmost', True)
                    self.root.overrideredirect(True)
                    
                    # Re-grab focus if lost
                    if self.root.focus_get() != self.entry:
                        self.entry.focus_set()
                    
                    # Prevent window from being minimized or hidden
                    self.root.deiconify()  # Restore if minimized
                    self.root.lift()       # Bring to front
                    
                    time.sleep(0.1)
                except:
                    pass
        
        threading.Thread(target=protection_loop, daemon=True).start()
    
    def on_entry_change(self, event):
        """Handle changes in the entry field"""
        current_text = self.entry.get().lower().strip()
        
        # Update status based on what they've typed
        if current_text == "":
            self.status_label.config(text='System locked! Authorization required!', fg='red')
        elif "im sorry" in current_text:
            self.status_label.config(text='Authorization accepted! Press Enter! 🟢', fg='green')
        elif "sorry" in current_text:
            self.status_label.config(text='Incomplete authorization... missing prefix', fg='orange')
        elif "im" in current_text:
            self.status_label.config(text='Partial code detected... continue typing', fg='orange')
        else:
            self.status_label.config(text='Invalid authorization code!', fg='red')
    
    def on_enter_pressed(self, event):
        """Handle Enter key press in entry field"""
        current_text = self.entry.get().lower().strip()
        if "im sorry" in current_text:
            self.exit_prank()
        else:
            # Make entry flash red
            original_bg = self.entry.cget('bg')
            self.entry.config(bg='red')
            self.root.after(200, lambda: self.entry.config(bg=original_bg))
            self.status_label.config(text='ACCESS DENIED! Invalid authorization!', fg='red')
    
    def on_key_press(self, event):
        """Handle key presses - user must type 'im sorry' to exit"""
        # Let the entry field handle the typing, but keep this for backup
        pass
    
    def show_typing_indicator(self):
        """Show what the user is typing - now handled by entry field"""
        pass
    
    def exit_prank(self, event=None):
        """Exit the program safely"""
        self.running = False
        self.effects_active = False
        
        # Show exit message
        try:
            messagebox.showinfo("System Test Complete", 
                              'This was a security awareness test.\n\n'
                              "Don't do it again.\n\n"
                              "Your system is secure.\n"
                              "No actual damage was done.")
        except:
            pass
        
        try:
            self.root.quit()
            self.root.destroy()
        except:
            pass
        
        sys.exit(0)

def main():
    """Main function to start the security test"""
    print("Initializing security breach simulation...")
    print("System vulnerability assessment in progress...")
    print('Emergency authorization protocol activated.')
    
    try:
        prank = FakeVirusPrank()
        prank.start_prank()
    except KeyboardInterrupt:
        print("\nSecurity test interrupted. System safe.")
        sys.exit(0)
    except Exception as e:
        print(f"System error: {e}")
        print("Security test terminated safely.")
        sys.exit(0)

if __name__ == "__main__":
    main()
