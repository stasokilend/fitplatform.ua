@echo off

set ANTHROPIC_BASE_URL=http://localhost:20128/v1
set ANTHROPIC_AUTH_TOKEN=sk-52450c0539086ff7-374a7c-6bd67c7d
set ANTHROPIC_API_KEY=
set ANTHROPIC_MODEL=kr/claude-sonnet-4.5
set ANTHROPIC_SMALL_FAST_MODEL=kr/claude-sonnet-4.5
set CLAUDE_CODE_DISABLE_NONESSENTIAL_TRAFFIC=1

tasklist /fi "imagename eq omniroute.exe" 2>nul | find /i "omniroute.exe" >nul
if errorlevel 1 (
    start "" omniroute
)

timeout /t 3 /nobreak >nul

claude %*