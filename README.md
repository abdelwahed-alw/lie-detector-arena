# 🕵️ Lie Detector Arena

A multiplayer AI-powered party game built with Laravel 11 where players submit 2 truths and 1 lie—and Claude AI decides who's bluffing. 

This project was built as a practical application of Laravel routing, Eloquent ORM, and third-party AI integration via the Anthropic API.

## 🎮 How It Works

1. **Submit:** Each player writes 2 true statements and 1 lie about themselves.
2. **AI Judges:** Claude (Anthropic AI) reads all three statements, assigns a suspicion percentage (0–100%) to each, and reveals which one it thinks is the lie along with a dramatic explanation.
3. **Class Votes:** The rest of the players vote on which statement they believe is the fabricated one.
4. **Score:** * **+150 points** for correctly guessing the lie.
   * **+100 points** to the author for every player they successfully fool.

## 🛠️ Tech Stack

* **Framework:** Laravel 11
* **Language:** PHP ≥ 8.2
* **Frontend:** Laravel Blade Templates
* **AI Integration:** Anthropic API (Claude 3.5 Haiku) via Laravel HTTP Client
* **Database:** SQLite / MySQL (via standard Laravel migrations)

## 🚀 Installation & Setup

### Prerequisites
* PHP >= 8.2
* Composer
* An Anthropic API Key for Claude
