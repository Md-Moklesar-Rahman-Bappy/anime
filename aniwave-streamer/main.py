import os
import re
from fastapi import FastAPI, HTTPException, Header, Response
from fastapi.responses import StreamingResponse
from telethon import TelegramClient

# Your Exact Credentials
API_ID = 35680962
API_HASH = "717e480054e3d46541974a9a154707a6"
CHANNEL_USERNAME = "aniwavebd"

app = FastAPI(title="AniWave Telegram Video Streamer")

# Initialize Telethon Client session
client = TelegramClient("aniwave_session", API_ID, API_HASH)

@app.on_event("startup")
async def startup_event():
    await client.start() # On first run, check your terminal to enter your login code

@app.get("/stream/{message_id}")
async def stream_video(message_id: int, range: str = Header(None)):
    if not await client.is_user_authorized():
        raise HTTPException(status_code=401, detail="Telegram Client Unauthorized")

    try:
        # Fetch the exact anime episode message from your channel
        msg = await client.get_messages(CHANNEL_USERNAME, ids=message_id)
        if not msg or not msg.video:
            raise HTTPException(status_code=404, detail="Video file not found in channel")
        
        video = msg.video
        file_size = video.size
        mime_type = video.mime_type or "video/mp4"

        # Handle Range Requests for Plyr.io seek/rewind mechanics
        start, end = 0, file_size - 1
        if range:
            match = re.match(r"bytes=(\d+)-(\d*)", range)
            if match:
                start = int(match.group(1))
                if match.group(2):
                    end = int(match.group(2))

        content_length = (end - start) + 1

        # Generator that streams video parts sequentially from Telegram API
        async def video_generator():
            # Telethon chunk download loop
            async for chunk in client.iter_download(video, offset=start, request_size=1024*1024):
                yield chunk

        headers = {
            "Content-Range": f"bytes {start}-{end}/{file_size}",
            "Accept-Ranges": "bytes",
            "Content-Length": str(content_length),
            "Content-Type": mime_type,
        }

        return StreamingResponse(video_generator(), status_code=206, headers=headers)

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
