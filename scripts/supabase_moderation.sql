-- Enable moderation columns and policies for Supabase
-- IMPORTANT: Replace :MODERATOR_USER_ID with an auth user id (profiles.user_id)

-- 1) Add columns if missing
ALTER TABLE public.spots
  ADD COLUMN IF NOT EXISTS user_id uuid,
  ADD COLUMN IF NOT EXISTS status text;

-- 2) Backfill status for existing rows
UPDATE public.spots
SET status = 'approved'
WHERE status IS NULL;

-- 3) Assign owner for existing rows
-- Replace the UUID below with a valid auth user id (profiles.user_id)
UPDATE public.spots
SET user_id = '9a41ca86-58c6-4d19-84d8-a6a0fa4f8c2a'
WHERE user_id IS NULL;

-- 4) Indexes
CREATE INDEX IF NOT EXISTS idx_spots_user_id ON public.spots(user_id);
CREATE INDEX IF NOT EXISTS idx_spots_status ON public.spots(status);

-- 5) RLS policies
ALTER TABLE public.spots ENABLE ROW LEVEL SECURITY;

-- Public can read approved
DROP POLICY IF EXISTS spots_read_approved ON public.spots;
CREATE POLICY spots_read_approved
  ON public.spots FOR SELECT
  USING (status = 'approved');

-- Owners can read their own spots (including pending)
DROP POLICY IF EXISTS spots_read_own ON public.spots;
CREATE POLICY spots_read_own
  ON public.spots FOR SELECT
  USING (auth.uid() = user_id);

-- Authenticated users can insert their own spots (pending only)
DROP POLICY IF EXISTS spots_insert_own ON public.spots;
CREATE POLICY spots_insert_own
  ON public.spots FOR INSERT
  WITH CHECK (auth.uid() = user_id AND status = 'pending');

-- Moderators/admins can insert approved spots
DROP POLICY IF EXISTS spots_insert_mod ON public.spots;
CREATE POLICY spots_insert_mod
  ON public.spots FOR INSERT
  WITH CHECK (
    auth.uid() = user_id
    AND status IN ('pending','approved')
    AND EXISTS (
      SELECT 1 FROM public.profiles p
      WHERE p.user_id = auth.uid()
        AND p.role IN ('moderator','admin')
    )
  );

-- Owners can update their own spots while status remains pending
DROP POLICY IF EXISTS spots_owner_write ON public.spots;
CREATE POLICY spots_owner_write
  ON public.spots FOR UPDATE
  USING (auth.uid() = user_id AND status = 'pending')
  WITH CHECK (auth.uid() = user_id AND status = 'pending');

DROP POLICY IF EXISTS spots_owner_delete ON public.spots;
CREATE POLICY spots_owner_delete
  ON public.spots FOR DELETE
  USING (auth.uid() = user_id);

-- Moderators/admins can read pending
DROP POLICY IF EXISTS spots_read_pending_mod ON public.spots;
CREATE POLICY spots_read_pending_mod
  ON public.spots FOR SELECT
  USING (
    status = 'pending'
    AND EXISTS (
      SELECT 1 FROM public.profiles p
      WHERE p.user_id = auth.uid()
        AND p.role IN ('moderator','admin')
    )
  );

-- Moderators/admins can update status
DROP POLICY IF EXISTS spots_moderate_update ON public.spots;
CREATE POLICY spots_moderate_update
  ON public.spots FOR UPDATE
  USING (
    EXISTS (
      SELECT 1 FROM public.profiles p
      WHERE p.user_id = auth.uid()
        AND p.role IN ('moderator','admin')
    )
  );
