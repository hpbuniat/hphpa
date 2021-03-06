<?php
/**
 * hphpa
 *
 * Copyright (c) 2012, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   hphpa
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright 2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.0
 */

namespace SebastianBergmann\HPHPA
{
    /**
     * Wrapper for HipHop's static analyzer.
     *
     * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
     * @copyright 2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
     * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
     * @link      http://github.com/sebastianbergmann/hphpa/tree
     * @since     Class available since Release 1.0.0
     */
    class Analyzer
    {
        /**
         * @param array  $files
         * @param Result $result
         */
        public function run(array $files, Result $result)
        {
            $tmpfname = tempnam('/tmp', 'hphp');
            file_put_contents($tmpfname, join("\n", $files));

            shell_exec(
              sprintf(
                'hphp -t analyze --input-list %s --output-dir %s --log 2 2>&1',
                $tmpfname,
                dirname($tmpfname)
              )
            );

            unlink($tmpfname);

            $codeError = dirname($tmpfname) . DIRECTORY_SEPARATOR . 'CodeError.js';
            $stats     = dirname($tmpfname) . DIRECTORY_SEPARATOR . 'Stats.js';

            if (!file_exists($codeError)) {
                throw new \RuntimeException(
                  'HipHop failed to complete static analysis.'
                );
            }

            $result->parse(json_decode(file_get_contents($codeError), TRUE));

            unlink($codeError);
            unlink($stats);
        }
    }
}
