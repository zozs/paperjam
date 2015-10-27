#!/usr/bin/env python2

from __future__ import print_function

import email.parser
import email.utils
import json
import hashlib
import poplib
import requests
import requests_kerberos

CONFIG_FILE = 'paperjam_mail.json'
CONTENT_TYPES = [
    'application/pdf',
    'image/jpeg',
    'image/png'
]


def load_settings(filename):
    """Load settings from JSON and returns config object."""
    with open(filename) as f:
        config = json.load(f)
        return config


def save_settings(conficg, filename):
    """Save settings to JSON."""
    with open(filename, 'w') as f:
        json.dump(config, f, indent=2)


def fetch_emails(config):
    """Retrieves the emails from a POP server and returns them."""
    pop_conn = poplib.POP3_SSL(config['pop']['host'], config['pop']['port'])
    pop_conn.user(config['pop']['user'])
    pop_conn.pass_(config['pop']['pass'])

    # Get messages from server
    messages = [pop_conn.retr(i) for i in range(1, len(pop_conn.list()[1]) + 1)]
    messages = ["\n".join(msg[1]) for msg in messages]
    messages = [email.parser.Parser().parsestr(msg) for msg in messages]
    
    pop_conn.quit()
    return messages


def find_new_emails(config, emails):
    """Walks through e-mails, hashes them, and checks whether such a hash
    already exists in the config file. Only return new ones."""
    #digest_message = ((hashlib.sha256(m.as_bytes()).hexdigest(), m) for m in emails) #Python 3
    digest_message = ((hashlib.sha256(m.as_string()).hexdigest(), m) for m in emails)
    return [(d, m) for d, m in digest_message if d not in config['hashes']]


def get_valid_attachments(email):
    """Get all valid attachments of a message through recursion."""
    if email.is_multipart():
        attachments = []
        for p in email.get_payload():
            attachments.extend(get_valid_attachments(p))
        return attachments
    else:
        # Check content type.
        if email.get_content_type() in CONTENT_TYPES:
            # Valid content-type.
            return [email.get_payload(decode=True)]
        else:
            print("Attachment with Content-Type " + email.get_content_type() + " ignored.")
            return []


def upload_attachment(config, attachment):
    """Upload a single attachment to paperjam."""
    add_url = config['paperjam'] + '/api/pages'
    files = {'file': ('file', attachment)}
    auth = requests_kerberos.HTTPKerberosAuth()
    ca_path = config['paperjam_ca']
    try:
        r = requests.post(add_url, files=files, auth=auth, verify=ca_path)
        if r.status_code == 200:
            # Upload succeeded!
            print("Upload of an attachment succeeded!")
            return True
        else:
            print("Upload failed! Got status code: " + r.status_code)
    except Exception as e:
        print("Upload failed because of exception " + str(e))
    return False # If we reach this it is because of failure.


def upload_emails(config, emails):
    """Uploads all attachments of each email to paperjam."""
    succeeded_digests = []
    for digest, mail in emails:
        # Only allow whitelisted senders.
        senders = email.utils.getaddresses(mail.get_all('from', []))
        senders_mail = [mailaddr for name, mailaddr in senders]
        valids = [(m in config['sender_whitelist']) for m in senders_mail]
        if not any(valids):
            print("No valid sender in From-header: " + str(senders_mail))
            continue
        # Find attachments (of correct type: jpg, png, or pdf)
        attachments = get_valid_attachments(mail)
        # Now upload each of them
        successes = [upload_attachment(config, a) for a in attachments]
        if all(successes):
            succeeded_digests.append(digest)
    
    # Return all digests of uploads that succeeded.
    return succeeded_digests


def append_new_emails(config, uploaded):
    """Appends all hashes in uploaded to the config."""
    config['hashes'].extend(uploaded)


if __name__ == '__main__': 
    config = load_settings(CONFIG_FILE)
    emails = fetch_emails(config)
    new_emails = find_new_emails(config, emails)
    uploaded_emails = upload_emails(config, new_emails)
    append_new_emails(config, uploaded_emails)
    save_settings(config, CONFIG_FILE)
