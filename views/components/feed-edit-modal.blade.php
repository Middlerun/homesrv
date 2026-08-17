<div class="modal-overlay" onclick="if(event.target===this) this.remove()">
    <div class="modal">
        <h2>Edit Calendar</h2>
        <form method="POST" action="/settings/edit-feed.php">
            <input type="hidden" name="feed_id" value="{{ $feed['id'] }}">
            <div class="form-group">
                <label for="feed_name">Name</label>
                <input type="text" id="feed_name" name="feed_name" value="{{ $feed['name'] }}" required>
            </div>
            <div class="form-group">
                <label for="feed_color">Color</label>
                <input type="color" id="feed_color" name="feed_color" value="{{ $feed['color'] }}" style="width: 60px; height: 36px; padding: 2px;">
            </div>
            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="document.querySelector('.modal-overlay').remove()">Cancel</button>
            </div>
        </form>
    </div>
</div>
