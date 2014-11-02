ALTER TABLE IFektest.llx_user ADD otp_seed VARCHAR(255) NULL;
ALTER TABLE ektest.llx_user ADD otp_counter INTEGER DEFAULT 0;